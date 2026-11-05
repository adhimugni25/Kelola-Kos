<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function __construct(private readonly BillingService $billing) {}

    /**
     * @param  array{kamar_id: int, tanggal_mulai: string, durasi_bulan: int, catatan?: ?string}  $data
     */
    public function buat(User $penghuni, array $data): Pemesanan
    {
        $kamar = Kamar::withoutGlobalScopes()->findOrFail($data['kamar_id']);

        return Pemesanan::create([
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'user_id' => $penghuni->id,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'durasi_bulan' => (int) $data['durasi_bulan'],
            'catatan' => $data['catatan'] ?? null,
            'status' => 'menunggu',
        ]);
    }

    public function setujui(Pemesanan $pemesanan, User $pengurus, string $tanggalMulai, int $durasiBulan): Pemesanan
    {
        return DB::transaction(function () use ($pemesanan, $pengurus, $tanggalMulai, $durasiBulan): Pemesanan {
            $kamar = Kamar::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($pemesanan->kamar_id);

            if ($pemesanan->status !== 'menunggu') {
                throw new RuntimeException('Pengajuan ini sudah diproses sebelumnya.');
            }

            if ($kamar->status !== 'kosong') {
                throw new RuntimeException('Kamar sudah tidak tersedia.');
            }

            $mulai = Carbon::parse($tanggalMulai);

            $pemesanan->update([
                'tanggal_mulai' => $mulai->toDateString(),
                'durasi_bulan' => $durasiBulan,
                'harga_bulanan' => $kamar->harga_bulanan,
                'berakhir_pada' => $mulai->copy()->addMonthsNoOverflow($durasiBulan)->toDateString(),
                'status' => 'aktif',
                'disetujui_oleh' => $pengurus->id,
                'disetujui_pada' => now(),
            ]);

            $kamar->update(['status' => 'terisi']);

            Pemesanan::withoutGlobalScopes()
                ->where('kamar_id', $kamar->id)
                ->where('id', '!=', $pemesanan->id)
                ->where('status', 'menunggu')
                ->update([
                    'status' => 'ditolak',
                    'alasan_penolakan' => 'Kamar sudah terisi',
                ]);

            $this->billing->buatTagihanPertama($pemesanan->fresh());

            return $pemesanan->fresh();
        });
    }

    public function tolak(Pemesanan $pemesanan, string $alasan): Pemesanan
    {
        if ($pemesanan->status !== 'menunggu') {
            throw new RuntimeException('Hanya pengajuan yang masih menunggu yang bisa ditolak.');
        }

        $pemesanan->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $alasan,
        ]);

        return $pemesanan;
    }

    public function batalkan(Pemesanan $pemesanan, string $alasan): Pemesanan
    {
        if ($pemesanan->status !== 'menunggu') {
            throw new RuntimeException('Pengajuan yang sudah disetujui tidak bisa dibatalkan sendiri.');
        }

        $pemesanan->update([
            'status' => 'dibatalkan',
            'alasan_penolakan' => $alasan,
        ]);

        return $pemesanan;
    }

    public function selesaikan(Pemesanan $pemesanan): Pemesanan
    {
        if ($pemesanan->status !== 'aktif') {
            throw new RuntimeException('Hanya pemesanan aktif yang bisa diselesaikan.');
        }

        $tagihanMenggantung = Tagihan::withoutGlobalScopes()
            ->where('pemesanan_id', $pemesanan->id)
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi'])
            ->exists();

        if ($tagihanMenggantung) {
            throw new RuntimeException('Selesaikan atau batalkan semua tagihan yang belum lunas terlebih dahulu.');
        }

        return DB::transaction(function () use ($pemesanan): Pemesanan {
            $pemesanan->update(['status' => 'selesai']);

            Kamar::withoutGlobalScopes()
                ->where('id', $pemesanan->kamar_id)
                ->update(['status' => 'kosong']);

            return $pemesanan->fresh();
        });
    }
}

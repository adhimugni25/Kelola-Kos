<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BillingService
{
    public function buatTagihanPertama(Pemesanan $pemesanan): Tagihan
    {
        return $this->buatTagihanBulan($pemesanan, now()->month, now()->year);
    }

    public function buatTagihanBulan(Pemesanan $pemesanan, int $bulan, int $tahun): Tagihan
    {
        return DB::transaction(function () use ($pemesanan, $bulan, $tahun): Tagihan {
            return Tagihan::withoutGlobalScopes()->firstOrCreate(
                [
                    'pemesanan_id' => $pemesanan->id,
                    'periode_bulan' => $bulan,
                    'periode_tahun' => $tahun,
                ],
                [
                    'kos_id' => $pemesanan->kos_id,
                    'nominal_sewa' => $pemesanan->harga_bulanan,
                    'biaya_tambahan' => 0,
                    'total' => $pemesanan->harga_bulanan,
                    'jatuh_tempo' => now()->addDays(7)->toDateString(),
                    'status' => 'belum_bayar',
                ]
            );
        });
    }

    /**
     * Membuat tagihan periode berjalan untuk semua sewa aktif milik satu kos.
     * Idempoten: pemanggilan berulang tidak menggandakan tagihan.
     */
    public function buatTagihanBulanIni(int $kosId): int
    {
        $pemesananAktif = Pemesanan::withoutGlobalScopes()
            ->where('kos_id', $kosId)
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', now()->endOfMonth()->toDateString())
            ->where(function ($query): void {
                $query->whereNull('berakhir_pada')
                    ->orWhere('berakhir_pada', '>=', now()->startOfMonth()->toDateString());
            })
            ->get();

        $jumlahDibuat = 0;

        foreach ($pemesananAktif as $pemesanan) {
            $tagihan = $this->buatTagihanBulan($pemesanan, now()->month, now()->year);

            if ($tagihan->wasRecentlyCreated) {
                $jumlahDibuat++;
            }
        }

        return $jumlahDibuat;
    }

    public function batalkan(Tagihan $tagihan): Tagihan
    {
        if (! in_array($tagihan->status, ['belum_bayar'], true)) {
            throw new RuntimeException('Tagihan yang sudah dibayar atau menunggu verifikasi tidak bisa dibatalkan.');
        }

        $tagihan->update(['status' => 'dibatalkan']);

        return $tagihan;
    }
}

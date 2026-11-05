<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function unggah(
        Tagihan $tagihan,
        int $metodePembayaranId,
        string $jumlah,
        UploadedFile $bukti,
        ?string $catatan,
    ): Pembayaran {
        return DB::transaction(function () use ($tagihan, $metodePembayaranId, $jumlah, $bukti, $catatan): Pembayaran {
            $terkunci = Tagihan::withoutGlobalScopes()->lockForUpdate()->findOrFail($tagihan->id);

            if ($terkunci->status !== 'belum_bayar') {
                throw new RuntimeException('Tagihan ini tidak bisa dibayar pada status saat ini.');
            }

            if (round((float) $jumlah, 2) !== round((float) $terkunci->total, 2)) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah pembayaran harus sama persis dengan total tagihan.',
                ]);
            }

            $path = $bukti->store('bukti/'.$terkunci->kos_id, 'bukti');

            $pembayaran = Pembayaran::create([
                'tagihan_id' => $terkunci->id,
                'metode_pembayaran_id' => $metodePembayaranId,
                'jumlah' => $terkunci->total,
                'bukti_path' => $path,
                'catatan_penghuni' => $catatan,
                'status' => 'menunggu',
            ]);

            $terkunci->update(['status' => 'menunggu_verifikasi']);

            return $pembayaran;
        });
    }

    public function terima(Pembayaran $pembayaran, User $pengurus): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $pengurus): Pembayaran {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);
            $tagihan = Tagihan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pembayaran->tagihan_id);

            if ($pembayaran->status !== 'menunggu') {
                throw new RuntimeException('Pembayaran ini sudah diverifikasi.');
            }

            $pembayaran->update([
                'status' => 'diterima',
                'diverifikasi_oleh' => $pengurus->id,
                'diverifikasi_pada' => now(),
            ]);

            Pembayaran::where('tagihan_id', $tagihan->id)
                ->where('id', '!=', $pembayaran->id)
                ->where('status', 'menunggu')
                ->update([
                    'status' => 'ditolak',
                    'catatan_verifikasi' => 'Pembayaran lain sudah diterima.',
                    'diverifikasi_oleh' => $pengurus->id,
                    'diverifikasi_pada' => now(),
                ]);

            $tagihan->update(['status' => 'lunas']);

            $this->catat($tagihan, $pengurus, 'pembayaran.diterima', [
                'pembayaran_id' => $pembayaran->id,
                'jumlah' => (string) $pembayaran->jumlah,
            ]);

            return $pembayaran->fresh();
        });
    }

    public function tolak(Pembayaran $pembayaran, User $pengurus, string $catatan): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran, $pengurus, $catatan): Pembayaran {
            $pembayaran = Pembayaran::lockForUpdate()->findOrFail($pembayaran->id);
            $tagihan = Tagihan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pembayaran->tagihan_id);

            if ($pembayaran->status !== 'menunggu') {
                throw new RuntimeException('Pembayaran ini sudah diverifikasi.');
            }

            $pembayaran->update([
                'status' => 'ditolak',
                'catatan_verifikasi' => $catatan,
                'diverifikasi_oleh' => $pengurus->id,
                'diverifikasi_pada' => now(),
            ]);

            $masihMenunggu = Pembayaran::where('tagihan_id', $tagihan->id)
                ->where('status', 'menunggu')
                ->exists();

            if (! $masihMenunggu) {
                $tagihan->update(['status' => 'belum_bayar']);
            }

            $this->catat($tagihan, $pengurus, 'pembayaran.ditolak', [
                'pembayaran_id' => $pembayaran->id,
                'catatan' => $catatan,
            ]);

            return $pembayaran->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private function catat(Tagihan $tagihan, User $pengurus, string $aksi, array $detail): void
    {
        AuditLog::create([
            'user_id' => $pengurus->id,
            'kos_id' => $tagihan->kos_id,
            'aksi' => $aksi,
            'entitas' => 'pembayaran',
            'entitas_id' => $detail['pembayaran_id'] ?? null,
            'detail' => $detail,
            'ip' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}

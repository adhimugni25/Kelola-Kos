<?php

namespace App\Services;

use App\Models\Pemesanan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function buatTagihanPertama(Pemesanan $pemesanan): Tagihan
    {
        return $this->buatTagihanBulan($pemesanan, now()->month, now()->year);
    }

    public function buatTagihanBulan(Pemesanan $pemesanan, int $bulan, int $tahun): Tagihan
    {
        return DB::transaction(function () use ($pemesanan, $bulan, $tahun): Tagihan {
            return Tagihan::firstOrCreate(
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
}

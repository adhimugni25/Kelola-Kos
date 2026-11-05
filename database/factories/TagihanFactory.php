<?php

namespace Database\Factories;

use App\Models\Pemesanan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tagihan>
 */
class TagihanFactory extends Factory
{
    public function definition(): array
    {
        $nominal = 900000;

        return [
            'kos_id' => fn (array $attributes): int => Pemesanan::findOrFail($attributes['pemesanan_id'])->kos_id,
            'pemesanan_id' => Pemesanan::factory()->aktif(),
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'nominal_sewa' => $nominal,
            'biaya_tambahan' => 0,
            'total' => $nominal,
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
        ];
    }
}

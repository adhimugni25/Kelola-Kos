<?php

namespace Database\Factories;

use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    public function definition(): array
    {
        $tagihan = Tagihan::factory()->create();

        return [
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => MetodePembayaran::factory()->create(['kos_id' => $tagihan->kos_id])->id,
            'jumlah' => $tagihan->total,
            'bukti_path' => 'bukti/contoh.jpg',
            'status' => 'menunggu',
        ];
    }
}

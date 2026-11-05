<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\MetodePembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetodePembayaran>
 */
class MetodePembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'tipe' => fake()->randomElement(['bank', 'ewallet']),
            'nama' => fake()->randomElement(['BCA', 'BRI', 'DANA', 'OVO']),
            'nomor' => fake()->numerify('##########'),
            'atas_nama' => fake()->name(),
            'is_active' => true,
        ];
    }
}

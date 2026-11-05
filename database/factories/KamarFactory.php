<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kamar>
 */
class KamarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'nomor' => fake()->unique()->bothify('K-###'),
            'tipe' => fake()->randomElement(['Standar', 'AC', 'VIP']),
            'harga_bulanan' => fake()->randomElement([700000, 900000, 1200000, 1500000]),
            'fasilitas' => 'Kasur, lemari, kamar mandi dalam',
            'status' => 'kosong',
        ];
    }

    public function terisi(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'terisi',
        ]);
    }

    public function perbaikan(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'perbaikan',
        ]);
    }
}

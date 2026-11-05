<?php

namespace Database\Factories;

use App\Models\Kos;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kos>
 */
class KosFactory extends Factory
{
    public function definition(): array
    {
        $nama = 'Kos '.fake()->streetName();

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.fake()->unique()->numerify('###'),
            'alamat' => fake()->address(),
            'kota' => fake()->city(),
            'deskripsi' => fake()->sentence(12),
            'kontak' => fake()->numerify('08##########'),
            'fasilitas_umum' => 'WiFi, dapur bersama, parkir',
            'is_active' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}

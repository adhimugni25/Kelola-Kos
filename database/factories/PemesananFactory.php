<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pemesanan>
 */
class PemesananFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'kamar_id' => fn (array $attributes): int => Kamar::factory()
                ->create(['kos_id' => $attributes['kos_id']])
                ->id,
            'user_id' => User::factory(),
            'tanggal_mulai' => now()->startOfMonth()->toDateString(),
            'durasi_bulan' => 12,
            'harga_bulanan' => 900000,
            'status' => 'menunggu',
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'aktif',
            'harga_bulanan' => 900000,
            'berakhir_pada' => now()->addMonths(12)->toDateString(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'penghuni',
            'phone' => fake()->numerify('08##########'),
            'kos_id' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'super_admin',
            'kos_id' => null,
        ]);
    }

    public function pengurus(Kos $kos): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'pengurus',
            'kos_id' => $kos->id,
        ]);
    }

    public function tidakAktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}

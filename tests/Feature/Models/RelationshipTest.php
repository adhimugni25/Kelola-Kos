<?php

namespace Tests\Feature\Models;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_kos_has_many_kamar(): void
    {
        $kos = Kos::factory()->create();
        Kamar::factory()->count(3)->create(['kos_id' => $kos->id]);

        $this->assertCount(3, $kos->kamar);
    }

    public function test_pemesanan_belongs_to_user_kamar_kos(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $user = User::factory()->create();
        $pemesanan = Pemesanan::factory()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $pemesanan->user->id);
        $this->assertEquals($kamar->id, $pemesanan->kamar->id);
        $this->assertEquals($kos->id, $pemesanan->kos->id);
    }

    public function test_user_has_many_pemesanan_and_belongs_to_kos(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->create(['role' => 'pengurus', 'kos_id' => $kos->id]);
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->assertEquals($kos->id, $pengurus->kos->id);
        $this->assertCount(0, $penghuni->pemesanan);
    }
}

<?php

namespace Tests\Feature\Public;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicKosTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_accessible(): void
    {
        $this->get('/')->assertOk()->assertSee('KelolaKos');
    }

    public function test_public_kos_page_shows_only_available_kamar(): void
    {
        $kos = Kos::factory()->create(['slug' => 'kos-melati']);

        Kamar::factory()->create(['kos_id' => $kos->id, 'nomor' => 'KOSONG-1', 'status' => 'kosong']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'nomor' => 'TERISI-1', 'status' => 'terisi']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'nomor' => 'RUSAK-1', 'status' => 'perbaikan']);

        $this->get('/k/kos-melati')
            ->assertOk()
            ->assertSee('KOSONG-1')
            ->assertDontSee('TERISI-1')
            ->assertDontSee('RUSAK-1');
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/k/tidak-ada')->assertNotFound();
    }

    public function test_inactive_kos_does_not_accept_booking(): void
    {
        Kos::factory()->nonaktif()->create(['slug' => 'kos-tutup']);
        Kamar::factory()->create(['kos_id' => Kos::where('slug', 'kos-tutup')->value('id')]);

        $this->get('/k/kos-tutup')
            ->assertOk()
            ->assertSee('belum menerima pemesanan');
    }

    public function test_guest_is_prompted_to_login_before_booking(): void
    {
        $kos = Kos::factory()->create(['slug' => 'kos-melati']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'kosong']);

        $this->get('/k/kos-melati')
            ->assertOk()
            ->assertSee('Masuk untuk mengajukan sewa');
    }
}

<?php

namespace Tests\Feature\Billing;

use App\Models\Kos;
use App\Models\MetodePembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetodePembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_can_create_payment_method_for_own_kos(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        $this->actingAs($pengurus)->post(route('pengurus.metode-pembayaran.store'), [
            'tipe' => 'bank',
            'nama' => 'BCA',
            'nomor' => '1234567890',
            'atas_nama' => 'Pemilik Kos',
            'is_active' => '1',
        ])->assertRedirect(route('pengurus.metode-pembayaran.index'));

        $this->assertDatabaseHas('metode_pembayaran', [
            'kos_id' => $kos->id,
            'nama' => 'BCA',
            'is_active' => true,
        ]);
    }

    public function test_pengurus_only_sees_methods_from_own_kos(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kosA)->create();

        MetodePembayaran::factory()->create(['kos_id' => $kosA->id, 'nama' => 'BCA-A']);
        MetodePembayaran::factory()->create(['kos_id' => $kosB->id, 'nama' => 'BCA-B']);

        $this->actingAs($pengurus)
            ->get(route('pengurus.metode-pembayaran.index'))
            ->assertOk()
            ->assertSee('BCA-A')
            ->assertDontSee('BCA-B');
    }

    public function test_pengurus_cannot_touch_method_from_other_kos(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kosA)->create();
        $metodeLain = MetodePembayaran::factory()->create(['kos_id' => $kosB->id]);

        $this->actingAs($pengurus)
            ->post(route('pengurus.metode-pembayaran.toggle', $metodeLain))
            ->assertNotFound();

        $this->actingAs($pengurus)
            ->delete(route('pengurus.metode-pembayaran.destroy', $metodeLain))
            ->assertNotFound();

        $this->assertDatabaseHas('metode_pembayaran', ['id' => $metodeLain->id]);
    }

    public function test_tipe_must_be_bank_or_ewallet(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        $this->actingAs($pengurus)->post(route('pengurus.metode-pembayaran.store'), [
            'tipe' => 'kripto',
            'nama' => 'Bitcoin',
            'nomor' => 'xyz',
            'atas_nama' => 'Pemilik Kos',
        ])->assertSessionHasErrors('tipe');

        $this->assertDatabaseCount('metode_pembayaran', 0);
    }
}

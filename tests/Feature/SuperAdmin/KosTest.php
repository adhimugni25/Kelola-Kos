<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_see_kos_list(): void
    {
        Kos::factory()->count(2)->create();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.kos.index'))
            ->assertOk()
            ->assertSee('Kos');
    }

    public function test_super_admin_can_create_kos(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('admin.kos.store'), [
            'nama' => 'Kos Melati',
            'slug' => 'kos-melati',
            'alamat' => 'Jl. Melati No. 1',
            'kota' => 'Bandung',
            'deskripsi' => 'Kos nyaman dekat kampus.',
            'kontak' => '081234567890',
            'fasilitas_umum' => 'WiFi, parkir',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.kos.index'));
        $this->assertDatabaseHas('kos', [
            'nama' => 'Kos Melati',
            'slug' => 'kos-melati',
            'is_active' => true,
        ]);
    }

    public function test_kos_slug_must_be_unique(): void
    {
        Kos::factory()->create(['slug' => 'kos-melati']);

        $response = $this->actingAs($this->superAdmin())->post(route('admin.kos.store'), [
            'nama' => 'Kos Melati Dua',
            'slug' => 'kos-melati',
            'alamat' => 'Jl. Melati No. 2',
            'kota' => 'Bandung',
            'kontak' => '081234567890',
        ]);

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('kos', 1);
    }

    public function test_super_admin_can_update_kos(): void
    {
        $kos = Kos::factory()->create(['nama' => 'Kos Lama']);

        $this->actingAs($this->superAdmin())->put(route('admin.kos.update', $kos), [
            'nama' => 'Kos Baru',
            'slug' => $kos->slug,
            'alamat' => $kos->alamat,
            'kota' => 'Yogyakarta',
            'kontak' => $kos->kontak,
        ])->assertRedirect(route('admin.kos.index'));

        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'nama' => 'Kos Baru', 'kota' => 'Yogyakarta']);
    }

    public function test_super_admin_can_deactivate_kos(): void
    {
        $kos = Kos::factory()->create(['is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.kos.toggle', $kos))
            ->assertRedirect(route('admin.kos.index'));

        $this->assertFalse($kos->fresh()->is_active);
    }

    public function test_pengurus_cannot_manage_kos(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        $this->actingAs($pengurus)->get(route('admin.kos.index'))->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pengurus/dashboard')->assertRedirect('/login');
    }

    public function test_penghuni_cannot_access_pengurus_routes(): void
    {
        $user = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($user)->get('/pengurus/dashboard')->assertForbidden();
    }

    public function test_pengurus_cannot_access_admin_routes(): void
    {
        $kos = Kos::factory()->create();
        $user = User::factory()->pengurus($kos)->create();

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_pengurus_can_access_pengurus_routes(): void
    {
        $kos = Kos::factory()->create();
        $user = User::factory()->pengurus($kos)->create();

        $this->actingAs($user)->get('/pengurus/dashboard')->assertOk();
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    }

    public function test_penghuni_can_access_penghuni_routes(): void
    {
        $user = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($user)->get('/penghuni/dashboard')->assertOk();
    }
}

<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengurusTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_create_pengurus_for_a_kos(): void
    {
        $kos = Kos::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('admin.pengurus.store'), [
            'name' => 'Pengurus Satu',
            'email' => 'pengurus1@example.com',
            'phone' => '081234567890',
            'kos_id' => $kos->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.pengurus.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'pengurus1@example.com',
            'role' => 'pengurus',
            'kos_id' => $kos->id,
        ]);
    }

    public function test_created_pengurus_password_is_hashed(): void
    {
        $kos = Kos::factory()->create();

        $this->actingAs($this->superAdmin())->post(route('admin.pengurus.store'), [
            'name' => 'Pengurus Satu',
            'email' => 'pengurus1@example.com',
            'phone' => '081234567890',
            'kos_id' => $kos->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'pengurus1@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_pengurus_email_must_be_unique(): void
    {
        $kos = Kos::factory()->create();
        User::factory()->create(['email' => 'pengurus1@example.com']);

        $response = $this->actingAs($this->superAdmin())->post(route('admin.pengurus.store'), [
            'name' => 'Pengurus Satu',
            'email' => 'pengurus1@example.com',
            'kos_id' => $kos->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_reset_pengurus_password(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create(['password' => 'Password123!']);

        $this->actingAs($this->superAdmin())->post(route('admin.pengurus.reset', $pengurus), [
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect(route('admin.pengurus.index'));

        $this->assertTrue(Hash::check('PasswordBaru123!', $pengurus->fresh()->password));
    }

    public function test_super_admin_can_deactivate_pengurus(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create(['is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.pengurus.toggle', $pengurus))
            ->assertRedirect(route('admin.pengurus.index'));

        $this->assertFalse($pengurus->fresh()->is_active);
    }

    public function test_super_admin_command_creates_hashed_account(): void
    {
        $this->artisan('user:create-super-admin', [
            'email' => 'admin@kelolakos.site',
            '--password' => 'SuperSecret123!',
        ])->assertSuccessful();

        $user = User::where('email', 'admin@kelolakos.site')->firstOrFail();

        $this->assertSame('super_admin', $user->role);
        $this->assertTrue(Hash::check('SuperSecret123!', $user->password));
    }

    public function test_super_admin_command_refuses_duplicate_email(): void
    {
        User::factory()->create(['email' => 'admin@kelolakos.site']);

        $this->artisan('user:create-super-admin', [
            'email' => 'admin@kelolakos.site',
            '--password' => 'SuperSecret123!',
        ])->assertFailed();
    }
}

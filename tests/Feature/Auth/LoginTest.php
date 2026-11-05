<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'pengurus@example.com',
            'password' => 'Password123!',
            'role' => 'penghuni',
        ]);

        $response = $this->post('/login', [
            'email' => 'pengurus@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/penghuni/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_each_role_is_redirected_to_its_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['password' => 'Password123!']);
        $pengurus = User::factory()->create(['password' => 'Password123!']);

        $this->post('/login', ['email' => $superAdmin->email, 'password' => 'Password123!'])
            ->assertRedirect('/admin/dashboard');
        $this->post('/logout');

        $this->post('/login', ['email' => $pengurus->email, 'password' => 'Password123!'])
            ->assertRedirect('/penghuni/dashboard');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Salah123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->tidakAktif()->create([
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }
}

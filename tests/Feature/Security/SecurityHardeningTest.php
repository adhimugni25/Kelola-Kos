<?php

namespace Tests\Feature\Security;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ])->assertStatus(419);
    }

    public function test_xss_payload_in_room_number_is_escaped(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        Kamar::factory()->create([
            'kos_id' => $kos->id,
            'nomor' => '<script>alert(1)</script>',
        ]);

        $response = $this->actingAs($pengurus)->get(route('pengurus.kamar.index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_sql_injection_payload_does_not_bypass_login(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $this->post('/login', [
            'email' => "' OR 1=1 --",
            'password' => 'apa saja',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sql_injection_payload_in_report_filter_is_rejected(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        $this->actingAs($pengurus)
            ->get(route('pengurus.laporan.index', [
                'dari' => "2026-01-01' OR '1'='1",
                'sampai' => '2026-01-31',
            ]))
            ->assertSessionHasErrors('dari');
    }

    public function test_sixth_failed_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'target@example.com',
            'password' => 'Password123!',
        ]);

        for ($percobaan = 0; $percobaan < 5; $percobaan++) {
            $this->post('/login', [
                'email' => 'target@example.com',
                'password' => 'Salah123!',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => 'target@example.com',
            'password' => 'Salah123!',
        ])->assertStatus(429);
    }

    public function test_session_is_invalidated_after_twelve_hours(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($penghuni)
            ->withSession(['login_at' => now()->subHours(13)->timestamp])
            ->get(route('penghuni.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_fresh_session_is_still_allowed(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($penghuni)
            ->withSession(['login_at' => now()->subHour()->timestamp])
            ->get(route('penghuni.dashboard'))
            ->assertOk();
    }

    public function test_content_security_policy_is_strict(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
    }

    public function test_hsts_header_is_only_sent_over_https(): void
    {
        $this->get('http://localhost/')->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/')->assertHeader(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains',
        );
    }
}

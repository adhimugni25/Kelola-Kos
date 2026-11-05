<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_has_skip_link_and_guest_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lewati ke konten')
            ->assertSee('Beranda')
            ->assertSee('Jaringan')
            ->assertSee('Kamar')
            ->assertSee('Pengurus')
            ->assertSee('FAQ')
            ->assertSee('Masuk')
            ->assertSee('Daftar');
    }

    public function test_public_navigation_has_accessible_mobile_drawer(): void
    {
        $this->get('/')
            ->assertSee('data-menu-toggle', false)
            ->assertSee('aria-controls="menu-publik"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="menu-publik" hidden', false);
    }
}

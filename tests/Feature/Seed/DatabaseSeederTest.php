<?php

namespace Tests\Feature\Seed;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_realistic_demo_data(): void
    {
        Storage::fake('bukti');

        $this->seed();

        $this->assertSame(2, Kos::count());
        $this->assertSame(12, Kamar::withoutGlobalScopes()->count());
        $this->assertSame(2, User::where('role', 'pengurus')->count());
        $this->assertSame(6, User::where('role', 'penghuni')->count());
        $this->assertSame(0, User::where('role', 'super_admin')->count());
        $this->assertSame(6, Pemesanan::withoutGlobalScopes()->where('status', 'aktif')->count());
        $this->assertSame(18, Tagihan::withoutGlobalScopes()->count());
        $this->assertTrue(Tagihan::withoutGlobalScopes()->where('status', 'belum_bayar')->exists());
        $this->assertTrue(Pembayaran::where('status', 'menunggu')->exists());
        $this->assertTrue(Pembayaran::where('status', 'diterima')->exists());
        $this->assertSame(6, Kamar::withoutGlobalScopes()->where('status', 'terisi')->count());
    }
}

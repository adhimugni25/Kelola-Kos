<?php

namespace Tests\Feature\Reports;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_occupancy_revenue_and_unpaid_summary(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        Kamar::factory()->count(2)->create(['kos_id' => $kos->id, 'status' => 'kosong']);
        $kamarTerisi = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'terisi']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'perbaikan']);

        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kamarTerisi->id,
            'user_id' => $penghuni->id,
            'harga_bulanan' => 900000,
        ]);

        $tagihan = Tagihan::factory()->create([
            'kos_id' => $kos->id,
            'pemesanan_id' => $pemesanan->id,
            'total' => 900000,
            'status' => 'lunas',
        ]);

        Tagihan::factory()->create([
            'kos_id' => $kos->id,
            'pemesanan_id' => $pemesanan->id,
            'periode_bulan' => 1,
            'periode_tahun' => 2026,
            'total' => 500000,
            'status' => 'belum_bayar',
        ]);

        $metode = MetodePembayaran::factory()->create(['kos_id' => $kos->id]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => $metode->id,
            'jumlah' => 900000,
            'status' => 'diterima',
            'diverifikasi_pada' => now(),
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => $metode->id,
            'jumlah' => 300000,
            'status' => 'diterima',
            'diverifikasi_pada' => now()->subMonthNoOverflow(),
        ]);

        $this->actingAs($pengurus)
            ->get(route('pengurus.dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', function (array $ringkasan): bool {
                return $ringkasan['total_kamar'] === 4
                    && $ringkasan['kamar_kosong'] === 2
                    && $ringkasan['kamar_terisi'] === 1
                    && $ringkasan['kamar_perbaikan'] === 1
                    && (float) $ringkasan['pendapatan_bulan_ini'] === 900000.0
                    && (float) $ringkasan['tagihan_belum_lunas'] === 500000.0;
            });
    }

    public function test_dashboard_does_not_include_other_kos_payments(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kosA)->create();
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $kosB->id,
            'user_id' => $penghuni->id,
            'harga_bulanan' => 750000,
        ]);

        $tagihan = Tagihan::factory()->create([
            'kos_id' => $kosB->id,
            'pemesanan_id' => $pemesanan->id,
            'total' => 750000,
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => MetodePembayaran::factory()->create(['kos_id' => $kosB->id])->id,
            'jumlah' => 750000,
            'status' => 'diterima',
            'diverifikasi_pada' => now(),
        ]);

        $this->actingAs($pengurus)
            ->get(route('pengurus.dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', fn (array $ringkasan): bool => (float) $ringkasan['pendapatan_bulan_ini'] === 0.0);
    }

    public function test_penghuni_cannot_access_pengurus_dashboard(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($penghuni)->get(route('pengurus.dashboard'))->assertForbidden();
    }
}

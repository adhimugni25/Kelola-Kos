<?php

namespace Tests\Feature\Billing;

use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTagihanTest extends TestCase
{
    use RefreshDatabase;

    public function test_buat_tagihan_bulan_ini_is_idempotent(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();

        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $kos->id,
            'harga_bulanan' => 900000,
            'berakhir_pada' => now()->addMonths(6)->toDateString(),
        ]);

        $pemesanan->kamar->update(['status' => 'terisi']);

        $this->actingAs($pengurus)
            ->post(route('pengurus.tagihan.bulan-ini'))
            ->assertRedirect(route('pengurus.tagihan.index'));

        $this->actingAs($pengurus)
            ->post(route('pengurus.tagihan.bulan-ini'))
            ->assertRedirect(route('pengurus.tagihan.index'));

        $this->assertSame(1, Tagihan::where('pemesanan_id', $pemesanan->id)->count());

        $tagihan = Tagihan::where('pemesanan_id', $pemesanan->id)->firstOrFail();

        $this->assertSame('900000.00', $tagihan->total);
        $this->assertSame(now()->month, $tagihan->periode_bulan);
        $this->assertSame('belum_bayar', $tagihan->status);
    }

    public function test_pengurus_can_create_single_tagihan(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();
        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $kos->id,
            'harga_bulanan' => 800000,
        ]);

        $this->actingAs($pengurus)->post(route('pengurus.tagihan.store'), [
            'pemesanan_id' => $pemesanan->id,
            'periode_bulan' => 12,
            'periode_tahun' => 2026,
            'nominal_sewa' => 800000,
            'biaya_tambahan' => 50000,
            'keterangan_tambahan' => 'WiFi',
            'jatuh_tempo' => '2026-12-10',
        ])->assertRedirect(route('pengurus.tagihan.index'));

        $this->assertDatabaseHas('tagihan', [
            'pemesanan_id' => $pemesanan->id,
            'periode_bulan' => 12,
            'periode_tahun' => 2026,
            'total' => 850000,
        ]);
    }

    public function test_tagihan_cannot_be_cancelled_after_being_paid(): void
    {
        $kos = Kos::factory()->create();
        $pengurus = User::factory()->pengurus($kos)->create();
        $tagihan = Tagihan::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'lunas',
        ]);

        $this->actingAs($pengurus)
            ->post(route('pengurus.tagihan.batal', $tagihan))
            ->assertSessionHas('error');

        $this->assertSame('lunas', $tagihan->fresh()->status);
    }
}

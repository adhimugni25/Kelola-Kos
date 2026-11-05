<?php

namespace Tests\Feature\Booking;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePemesananTest extends TestCase
{
    use RefreshDatabase;

    private Kos $kos;

    private User $penghuni;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kos = Kos::factory()->create();
        $this->penghuni = User::factory()->create(['role' => 'penghuni']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'kosong',
            'harga_bulanan' => 900000,
        ]);
    }

    public function test_penghuni_can_create_pemesanan(): void
    {
        $this->actingAs($this->penghuni)->post(route('penghuni.pemesanan.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => now()->addDays(3)->toDateString(),
            'durasi_bulan' => 12,
            'catatan' => 'Mohon kamar dekat jendela.',
        ])->assertRedirect(route('penghuni.pemesanan.index'));

        $this->assertDatabaseHas('pemesanan', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'user_id' => $this->penghuni->id,
            'status' => 'menunggu',
            'durasi_bulan' => 12,
        ]);

        $this->assertSame('kosong', $this->kamar->fresh()->status);
    }

    public function test_guest_cannot_create_pemesanan(): void
    {
        $this->post(route('penghuni.pemesanan.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 1,
        ])->assertRedirect(route('login'));
    }

    public function test_cannot_book_occupied_kamar(): void
    {
        $this->kamar->update(['status' => 'terisi']);

        $this->actingAs($this->penghuni)->post(route('penghuni.pemesanan.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 12,
        ])->assertSessionHasErrors('kamar_id');

        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_penghuni_cannot_have_two_active_or_pending_bookings(): void
    {
        Pemesanan::factory()->create([
            'kos_id' => $this->kos->id,
            'user_id' => $this->penghuni->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->penghuni)->post(route('penghuni.pemesanan.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 12,
        ])->assertSessionHasErrors();

        $this->assertSame(1, Pemesanan::where('user_id', $this->penghuni->id)->count());
    }

    public function test_cannot_book_kamar_from_inactive_kos(): void
    {
        $this->kos->update(['is_active' => false]);

        $this->actingAs($this->penghuni)->post(route('penghuni.pemesanan.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 12,
        ])->assertSessionHasErrors('kamar_id');

        $this->assertDatabaseCount('pemesanan', 0);
    }

    public function test_penghuni_can_cancel_pending_pemesanan(): void
    {
        $pemesanan = Pemesanan::factory()->create([
            'kos_id' => $this->kos->id,
            'user_id' => $this->penghuni->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($this->penghuni)
            ->post(route('penghuni.pemesanan.batal', $pemesanan))
            ->assertRedirect(route('penghuni.pemesanan.index'));

        $this->assertSame('dibatalkan', $pemesanan->fresh()->status);
        $this->assertNotNull($pemesanan->fresh()->alasan_penolakan);
    }
}

<?php

namespace Tests\Feature\Booking;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveRejectTest extends TestCase
{
    use RefreshDatabase;

    private Kos $kos;

    private User $pengurus;

    private Kamar $kamar;

    private User $penghuni;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kos = Kos::factory()->create();
        $this->pengurus = User::factory()->pengurus($this->kos)->create();
        $this->penghuni = User::factory()->create(['role' => 'penghuni']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'kosong',
            'harga_bulanan' => 900000,
        ]);
    }

    private function pengajuan(array $atribut = []): Pemesanan
    {
        return Pemesanan::factory()->create(array_merge([
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'user_id' => $this->penghuni->id,
            'tanggal_mulai' => '2026-11-01',
            'durasi_bulan' => 12,
            'status' => 'menunggu',
        ], $atribut));
    }

    public function test_pengurus_can_approve_pemesanan(): void
    {
        $pemesanan = $this->pengajuan();

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.setujui', $pemesanan), [
                'tanggal_mulai' => '2026-11-01',
                'durasi_bulan' => 12,
            ])
            ->assertRedirect(route('pengurus.pemesanan.index'));

        $pemesanan->refresh();

        $this->assertSame('aktif', $pemesanan->status);
        $this->assertSame('900000.00', $pemesanan->harga_bulanan);
        $this->assertSame('2027-11-01', $pemesanan->berakhir_pada->toDateString());
        $this->assertSame($this->pengurus->id, $pemesanan->disetujui_oleh);
        $this->assertSame('terisi', $this->kamar->fresh()->status);
        $this->assertSame(1, Tagihan::where('pemesanan_id', $pemesanan->id)->count());
    }

    public function test_approval_rejects_other_pending_bookings_for_same_kamar(): void
    {
        $pemesanan = $this->pengajuan();
        $lain = $this->pengajuan(['user_id' => User::factory()->create(['role' => 'penghuni'])->id]);

        $this->actingAs($this->pengurus)->post(route('pengurus.pemesanan.setujui', $pemesanan), [
            'tanggal_mulai' => '2026-11-01',
            'durasi_bulan' => 12,
        ]);

        $this->assertSame('ditolak', $lain->fresh()->status);
        $this->assertSame('Kamar sudah terisi', $lain->fresh()->alasan_penolakan);
    }

    public function test_cannot_approve_pemesanan_when_kamar_already_occupied(): void
    {
        $pemesanan = $this->pengajuan();
        $this->kamar->update(['status' => 'terisi']);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.setujui', $pemesanan), [
                'tanggal_mulai' => '2026-11-01',
                'durasi_bulan' => 12,
            ])
            ->assertSessionHas('error');

        $this->assertSame('menunggu', $pemesanan->fresh()->status);
    }

    public function test_pengurus_from_other_kos_cannot_approve(): void
    {
        $pemesanan = $this->pengajuan();
        $kosLain = Kos::factory()->create();
        $pengurusLain = User::factory()->pengurus($kosLain)->create();

        $this->actingAs($pengurusLain)
            ->post(route('pengurus.pemesanan.setujui', $pemesanan), [
                'tanggal_mulai' => '2026-11-01',
                'durasi_bulan' => 12,
            ])
            ->assertNotFound();

        $this->assertSame('menunggu', $pemesanan->fresh()->status);
    }

    public function test_reject_requires_reason_and_stores_it(): void
    {
        $pemesanan = $this->pengajuan();

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.tolak', $pemesanan), ['alasan_penolakan' => ''])
            ->assertSessionHasErrors('alasan_penolakan');

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.tolak', $pemesanan), [
                'alasan_penolakan' => 'Kamar sedang diperbaiki.',
            ])
            ->assertRedirect(route('pengurus.pemesanan.index'));

        $this->assertSame('ditolak', $pemesanan->fresh()->status);
        $this->assertSame('Kamar sedang diperbaiki.', $pemesanan->fresh()->alasan_penolakan);
    }

    public function test_pengurus_can_finish_pemesanan_without_outstanding_bills(): void
    {
        $pemesanan = $this->pengajuan(['status' => 'aktif', 'berakhir_pada' => '2027-11-01']);
        $this->kamar->update(['status' => 'terisi']);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.selesai', $pemesanan))
            ->assertRedirect(route('pengurus.pemesanan.index'));

        $this->assertSame('selesai', $pemesanan->fresh()->status);
        $this->assertSame('kosong', $this->kamar->fresh()->status);
    }

    public function test_cannot_finish_pemesanan_with_unpaid_bills(): void
    {
        $pemesanan = $this->pengajuan(['status' => 'aktif', 'berakhir_pada' => '2027-11-01']);
        $this->kamar->update(['status' => 'terisi']);
        Tagihan::factory()->create([
            'kos_id' => $this->kos->id,
            'pemesanan_id' => $pemesanan->id,
            'status' => 'belum_bayar',
        ]);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pemesanan.selesai', $pemesanan))
            ->assertSessionHas('error');

        $this->assertSame('aktif', $pemesanan->fresh()->status);
        $this->assertSame('terisi', $this->kamar->fresh()->status);
    }
}

<?php

namespace Tests\Feature\Payment;

use App\Models\Kos;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private Kos $kos;

    private User $pengurus;

    private User $penghuni;

    private Tagihan $tagihan;

    private MetodePembayaran $metode;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('bukti');

        $this->kos = Kos::factory()->create();
        $this->pengurus = User::factory()->pengurus($this->kos)->create();
        $this->penghuni = User::factory()->create(['role' => 'penghuni']);

        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $this->kos->id,
            'user_id' => $this->penghuni->id,
            'harga_bulanan' => 900000,
        ]);

        $this->tagihan = Tagihan::factory()->create([
            'kos_id' => $this->kos->id,
            'pemesanan_id' => $pemesanan->id,
            'nominal_sewa' => 900000,
            'total' => 900000,
            'status' => 'belum_bayar',
        ]);

        $this->metode = MetodePembayaran::factory()->create(['kos_id' => $this->kos->id]);
    }

    public function test_penghuni_can_upload_payment_proof(): void
    {
        $this->actingAs($this->penghuni)->post(route('penghuni.tagihan.bayar', $this->tagihan), [
            'metode_pembayaran_id' => $this->metode->id,
            'jumlah' => 900000,
            'catatan_penghuni' => 'Transfer dari BCA.',
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('penghuni.tagihan.show', $this->tagihan));

        $pembayaran = Pembayaran::firstOrFail();

        $this->assertSame('menunggu', $pembayaran->status);
        $this->assertSame('menunggu_verifikasi', $this->tagihan->fresh()->status);
        Storage::disk('bukti')->assertExists($pembayaran->bukti_path);
    }

    public function test_upload_rejects_amount_that_does_not_match_total(): void
    {
        $this->actingAs($this->penghuni)->post(route('penghuni.tagihan.bayar', $this->tagihan), [
            'metode_pembayaran_id' => $this->metode->id,
            'jumlah' => 500000,
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('pembayaran', 0);
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
    }

    public function test_upload_rejects_non_image_file(): void
    {
        $this->actingAs($this->penghuni)->post(route('penghuni.tagihan.bayar', $this->tagihan), [
            'metode_pembayaran_id' => $this->metode->id,
            'jumlah' => 900000,
            'bukti' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('bukti');

        $this->assertDatabaseCount('pembayaran', 0);
    }

    public function test_penghuni_cannot_upload_to_other_penghuni_bill(): void
    {
        $lain = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($lain)->post(route('penghuni.tagihan.bayar', $this->tagihan), [
            'metode_pembayaran_id' => $this->metode->id,
            'jumlah' => 900000,
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertForbidden();
    }

    public function test_pengurus_can_accept_payment(): void
    {
        $pembayaran = $this->buatPembayaran();

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pembayaran.terima', $pembayaran))
            ->assertRedirect(route('pengurus.pembayaran.index'));

        $this->assertSame('diterima', $pembayaran->fresh()->status);
        $this->assertSame('lunas', $this->tagihan->fresh()->status);
        $this->assertNotNull($pembayaran->fresh()->diverifikasi_pada);
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'pembayaran.diterima',
            'entitas' => 'pembayaran',
            'entitas_id' => $pembayaran->id,
        ]);
    }

    public function test_pengurus_can_reject_payment_with_note(): void
    {
        $pembayaran = $this->buatPembayaran();

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.pembayaran.tolak', $pembayaran), [
                'catatan_verifikasi' => 'Bukti tidak terbaca.',
            ])
            ->assertRedirect(route('pengurus.pembayaran.index'));

        $this->assertSame('ditolak', $pembayaran->fresh()->status);
        $this->assertSame('belum_bayar', $this->tagihan->fresh()->status);
        $this->assertSame('Bukti tidak terbaca.', $pembayaran->fresh()->catatan_verifikasi);
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'pembayaran.ditolak',
            'entitas' => 'pembayaran',
            'entitas_id' => $pembayaran->id,
        ]);
    }

    public function test_pengurus_from_other_kos_cannot_verify(): void
    {
        $pembayaran = $this->buatPembayaran();
        $kosLain = Kos::factory()->create();
        $pengurusLain = User::factory()->pengurus($kosLain)->create();

        $this->actingAs($pengurusLain)
            ->post(route('pengurus.pembayaran.terima', $pembayaran))
            ->assertNotFound();

        $this->assertSame('menunggu', $pembayaran->fresh()->status);
    }

    public function test_payment_proof_is_only_served_to_owner_and_pengurus(): void
    {
        $pembayaran = $this->buatPembayaran();
        $orangLain = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($this->penghuni)
            ->get(route('pembayaran.bukti', $pembayaran))
            ->assertOk();

        $this->actingAs($this->pengurus)
            ->get(route('pembayaran.bukti', $pembayaran))
            ->assertOk();

        $this->actingAs($orangLain)
            ->get(route('pembayaran.bukti', $pembayaran))
            ->assertForbidden();
    }

    private function buatPembayaran(): Pembayaran
    {
        $path = 'bukti/'.$this->kos->id.'/contoh.jpg';
        Storage::disk('bukti')->put($path, 'contoh-bukti');

        return Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'metode_pembayaran_id' => $this->metode->id,
            'jumlah' => 900000,
            'bukti_path' => $path,
            'status' => 'menunggu',
        ]);
    }
}

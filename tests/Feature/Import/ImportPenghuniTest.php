<?php

namespace Tests\Feature\Import;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ImportPenghuniTest extends TestCase
{
    use RefreshDatabase;

    private Kos $kos;

    private User $pengurus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kos = Kos::factory()->create();
        $this->pengurus = User::factory()->pengurus($this->kos)->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_template_penghuni_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->get(route('pengurus.impor.penghuni.template'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $isi = $response->streamedContent();

        $this->assertStringContainsString('nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan', $isi);
    }

    public function test_pengurus_can_import_active_penghuni(): void
    {
        Carbon::setTestNow('2026-05-10 08:00:00');

        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'harga_bulanan' => 900000,
            'status' => 'kosong',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi Santoso,budi@example.com,08123456789,A1,2026-04-10,6
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertRedirect(route('pengurus.impor.index'))
            ->assertSessionHas('status')
            ->assertSessionHas('kredensial');

        $akun = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame('penghuni', $akun->role);
        $this->assertSame('Budi Santoso', $akun->name);
        $this->assertSame('08123456789', $akun->phone);

        $pemesanan = Pemesanan::withoutGlobalScopes()->where('user_id', $akun->id)->firstOrFail();

        $this->assertSame('aktif', $pemesanan->status);
        $this->assertSame($kamar->id, $pemesanan->kamar_id);
        $this->assertSame($this->pengurus->id, $pemesanan->disetujui_oleh);
        $this->assertSame('2026-04-10', $pemesanan->tanggal_mulai->toDateString());
        $this->assertSame('2026-10-10', $pemesanan->berakhir_pada->toDateString());
        $this->assertSame('900000.00', (string) $pemesanan->harga_bulanan);
        $this->assertSame('terisi', $kamar->fresh()->status);
        $this->assertSame(0, Tagihan::withoutGlobalScopes()->count());
    }

    public function test_import_rejects_occupied_room_without_creating_anything(): void
    {
        Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'status' => 'terisi',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi,budi@example.com,08123,A1,2026-04-10,6
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertSessionHasErrors('impor');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
        $this->assertSame(0, Pemesanan::withoutGlobalScopes()->count());
    }

    public function test_import_rejects_existing_email_with_different_name(): void
    {
        User::factory()->create([
            'name' => 'Budi Lama',
            'email' => 'budi@example.com',
            'role' => 'penghuni',
        ]);

        Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'status' => 'kosong',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi Baru,budi@example.com,08123,A1,2026-04-10,6
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertSessionHasErrors('impor');

        $this->assertSame(1, User::where('email', 'budi@example.com')->count());
        $this->assertSame(0, Pemesanan::withoutGlobalScopes()->count());
    }

    public function test_import_reuses_existing_account_with_same_name(): void
    {
        $akun = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'penghuni',
        ]);

        Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'status' => 'kosong',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi Santoso,budi@example.com,08123,A1,2026-04-10,6
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertSessionHas('status')
            ->assertSessionMissing('kredensial');

        $this->assertSame(1, User::where('email', 'budi@example.com')->count());
        $this->assertSame(1, Pemesanan::withoutGlobalScopes()->where('user_id', $akun->id)->count());
    }

    public function test_import_rejects_future_start_date_and_invalid_duration(): void
    {
        Carbon::setTestNow('2026-05-10 08:00:00');

        Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'status' => 'kosong',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi,budi@example.com,08123,A1,2026-06-10,30
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertSessionHasErrors('impor');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
        $this->assertSame(0, Pemesanan::withoutGlobalScopes()->count());
    }

    public function test_import_is_rate_limited_to_three_uploads_per_hour(): void
    {
        Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'status' => 'kosong',
        ]);

        $berkas = $this->buatCsv(<<<'CSV'
        nama,email,no_hp,nomor_kamar,tanggal_mulai,durasi_bulan
        Budi,budi@example.com,08123,A1,2026-04-10,6
        CSV);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->pengurus)
                ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
                ->assertStatus(302);
        }

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.penghuni'), ['berkas' => $berkas])
            ->assertStatus(429);
    }

    private function buatCsv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('penghuni.csv', $isi);
    }
}

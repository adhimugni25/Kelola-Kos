<?php

namespace Tests\Feature\Import;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportKamarTest extends TestCase
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

    public function test_template_kamar_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->pengurus)
            ->get(route('pengurus.impor.kamar.template'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $isi = $response->streamedContent();

        $this->assertStringContainsString('nomor,tipe,harga_bulanan,fasilitas,status', $isi);
        $this->assertStringContainsString('A1', $isi);
    }

    public function test_pengurus_can_import_kamar_from_csv(): void
    {
        $berkas = $this->buatCsv(<<<'CSV'
        nomor,tipe,harga_bulanan,fasilitas,status
        A1,Standard,900000,"AC, WiFi",kosong
        A2,Deluxe,1200000,AC+KM Dalam,perbaikan
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.kamar'), ['berkas' => $berkas])
            ->assertRedirect(route('pengurus.impor.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('kamar', [
            'kos_id' => $this->kos->id,
            'nomor' => 'A1',
            'tipe' => 'Standard',
            'fasilitas' => 'AC, WiFi',
            'status' => 'kosong',
        ]);
        $this->assertDatabaseHas('kamar', [
            'kos_id' => $this->kos->id,
            'nomor' => 'A2',
            'status' => 'perbaikan',
        ]);
        $this->assertSame(2, Kamar::withoutGlobalScopes()->where('kos_id', $this->kos->id)->count());
    }

    public function test_import_previews_errors_without_inserting_any_row(): void
    {
        Kamar::factory()->create(['kos_id' => $this->kos->id, 'nomor' => 'A1']);

        $berkas = $this->buatCsv(<<<'CSV'
        nomor,tipe,harga_bulanan,fasilitas,status
        A1,Standard,900000,AC,kosong
        A2,Standard,-5,AC,rusak
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.kamar'), ['berkas' => $berkas])
            ->assertRedirect(route('pengurus.impor.index'))
            ->assertSessionHasErrors('impor');

        $this->assertSame(1, Kamar::withoutGlobalScopes()->where('kos_id', $this->kos->id)->count());
    }

    public function test_duplicate_nomor_within_file_is_rejected(): void
    {
        $berkas = $this->buatCsv(<<<'CSV'
        nomor,tipe,harga_bulanan,fasilitas,status
        B1,Standard,900000,AC,kosong
        b1,Standard,900000,AC,kosong
        CSV);

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.kamar'), ['berkas' => $berkas])
            ->assertSessionHasErrors('impor');

        $this->assertSame(0, Kamar::withoutGlobalScopes()->where('kos_id', $this->kos->id)->count());
    }

    public function test_berkas_must_be_a_csv_file(): void
    {
        $berkas = UploadedFile::fake()->create('kamar.txt', 10, 'text/plain');

        $this->actingAs($this->pengurus)
            ->post(route('pengurus.impor.kamar'), ['berkas' => $berkas])
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, Kamar::withoutGlobalScopes()->where('kos_id', $this->kos->id)->count());
    }

    public function test_penghuni_cannot_access_import_pages(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($penghuni)->get(route('pengurus.impor.index'))->assertForbidden();
        $this->actingAs($penghuni)->get(route('pengurus.impor.kamar.template'))->assertForbidden();
        $this->actingAs($penghuni)
            ->post(route('pengurus.impor.kamar'), ['berkas' => $this->buatCsv('nomor,tipe,harga_bulanan,fasilitas,status')])
            ->assertForbidden();
    }

    private function buatCsv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('kamar.csv', $isi);
    }
}

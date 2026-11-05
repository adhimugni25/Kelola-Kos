<?php

namespace Tests\Feature\Pengurus;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KamarTest extends TestCase
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

    public function test_pengurus_can_see_own_kamar_list(): void
    {
        Kamar::factory()->create(['kos_id' => $this->kos->id, 'nomor' => 'A-1']);

        $this->actingAs($this->pengurus)
            ->get(route('pengurus.kamar.index'))
            ->assertOk()
            ->assertSee('A-1');
    }

    public function test_pengurus_can_create_kamar(): void
    {
        $this->actingAs($this->pengurus)->post(route('pengurus.kamar.store'), [
            'nomor' => 'A-2',
            'tipe' => 'AC',
            'harga_bulanan' => 950000,
            'fasilitas' => 'Kasur, AC',
            'status' => 'kosong',
        ])->assertRedirect(route('pengurus.kamar.index'));

        $this->assertDatabaseHas('kamar', [
            'kos_id' => $this->kos->id,
            'nomor' => 'A-2',
            'harga_bulanan' => 950000,
        ]);
    }

    public function test_nomor_kamar_must_be_unique_per_kos(): void
    {
        Kamar::factory()->create(['kos_id' => $this->kos->id, 'nomor' => 'A-2']);

        $this->actingAs($this->pengurus)->post(route('pengurus.kamar.store'), [
            'nomor' => 'A-2',
            'tipe' => 'AC',
            'harga_bulanan' => 950000,
            'status' => 'kosong',
        ])->assertSessionHasErrors('nomor');

        $this->assertDatabaseCount('kamar', 1);
    }

    public function test_pengurus_can_update_kamar(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'nomor' => 'A-3']);

        $this->actingAs($this->pengurus)->put(route('pengurus.kamar.update', $kamar), [
            'nomor' => 'A-3',
            'tipe' => 'VIP',
            'harga_bulanan' => 1500000,
            'status' => 'perbaikan',
        ])->assertRedirect(route('pengurus.kamar.index'));

        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'tipe' => 'VIP',
            'status' => 'perbaikan',
        ]);
    }

    public function test_pengurus_cannot_access_kamar_from_another_kos(): void
    {
        $kosLain = Kos::factory()->create();
        $kamarLain = Kamar::factory()->create(['kos_id' => $kosLain->id]);

        $this->actingAs($this->pengurus)
            ->get(route('pengurus.kamar.edit', $kamarLain))
            ->assertNotFound();

        $this->actingAs($this->pengurus)->put(route('pengurus.kamar.update', $kamarLain), [
            'nomor' => 'X-1',
            'tipe' => 'AC',
            'harga_bulanan' => 1000000,
            'status' => 'kosong',
        ])->assertNotFound();

        $this->assertNotSame('X-1', $kamarLain->fresh()->nomor);
    }

    public function test_pengurus_can_upload_foto_kamar(): void
    {
        Storage::fake('public');

        $this->actingAs($this->pengurus)->post(route('pengurus.kamar.store'), [
            'nomor' => 'A-9',
            'tipe' => 'AC',
            'harga_bulanan' => 950000,
            'status' => 'kosong',
            'foto' => UploadedFile::fake()->image('kamar.jpg'),
        ])->assertRedirect(route('pengurus.kamar.index'));

        $kamar = Kamar::where('nomor', 'A-9')->firstOrFail();

        $this->assertNotNull($kamar->foto_path);
        Storage::disk('public')->assertExists($kamar->foto_path);
    }

    public function test_upload_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->pengurus)->post(route('pengurus.kamar.store'), [
            'nomor' => 'A-10',
            'tipe' => 'AC',
            'harga_bulanan' => 950000,
            'status' => 'kosong',
            'foto' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('foto');
    }

    public function test_pengurus_can_delete_empty_kamar(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $this->actingAs($this->pengurus)
            ->delete(route('pengurus.kamar.destroy', $kamar))
            ->assertRedirect(route('pengurus.kamar.index'));

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
    }

    public function test_kamar_with_active_pemesanan_cannot_be_deleted(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        Pemesanan::factory()->aktif()->create([
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
        ]);

        $this->actingAs($this->pengurus)
            ->delete(route('pengurus.kamar.destroy', $kamar))
            ->assertRedirect(route('pengurus.kamar.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }
}

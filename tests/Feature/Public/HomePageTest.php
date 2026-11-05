<?php

namespace Tests\Feature\Public;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pemesanan;
use App\Models\User;
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

    public function test_homepage_uses_real_aggregates_and_skips_inactive_kos(): void
    {
        $melati = $this->buatKos('Kos Melati', 'Yogyakarta', [
            'A1' => ['terisi', 900000],
            'A2' => ['terisi', 1000000],
            'A3' => ['perbaikan', 1100000],
            'A4' => ['kosong', 950000],
            'A5' => ['kosong', 1050000],
        ]);
        $anggrek = $this->buatKos('Kos Anggrek', 'Yogyakarta', [
            'B1' => ['terisi', 900000],
            'B2' => ['kosong', 1200000],
        ]);
        $this->buatKos('Kos Kenanga', 'Jakarta', [
            'C1' => ['kosong', 800000],
        ]);
        $this->buatKos('Kos Tutup', 'Bandung', [
            'D1' => ['kosong', 700000],
        ], false);

        foreach ([[$melati, 'A1'], [$anggrek, 'B1']] as [$kos, $nomor]) {
            Pemesanan::factory()->aktif()->create([
                'kos_id' => $kos->id,
                'kamar_id' => $kos->kamar()->where('nomor', $nomor)->value('id'),
                'user_id' => User::factory()->create()->id,
            ]);
        }

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('jumlahKota', 2);
        $response->assertViewHas('jumlahKos', 3);
        $response->assertViewHas('jumlahKamar', 8);
        $response->assertViewHas('jumlahKosong', 4);
        $response->assertViewHas('ringkasanKamar', ['terisi' => 3, 'perbaikan' => 1, 'kosong' => 4]);
        $response->assertViewHas(
            'kamarKosong',
            fn ($kamar) => $kamar->pluck('nomor')->all() === ['C1', 'A4', 'A5'],
        );
    }

    public function test_homepage_aggregates_include_all_kos_for_logged_in_pengurus(): void
    {
        $melati = $this->buatKos('Kos Melati', 'Yogyakarta', [
            'A1' => ['terisi', 900000],
            'A2' => ['kosong', 950000],
        ]);
        $this->buatKos('Kos Anggrek', 'Yogyakarta', [
            'B1' => ['terisi', 900000],
            'B2' => ['kosong', 1200000],
        ]);

        $pengurus = User::factory()->pengurus($melati)->create();

        $this->actingAs($pengurus)->get('/')
            ->assertOk()
            ->assertViewHas('jumlahKos', 2)
            ->assertViewHas('jumlahKamar', 4)
            ->assertViewHas('kamarKosong', fn ($kamar) => $kamar->pluck('nomor')->contains('B2'));
    }

    public function test_homepage_handles_empty_database(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewHas('jumlahKos', 0)
            ->assertViewHas('jumlahKota', 0)
            ->assertViewHas('kamarKosong', fn ($kamar) => $kamar->isEmpty());
    }

    private function buatKos(string $nama, string $kota, array $kamar, bool $aktif = true): Kos
    {
        $kos = Kos::factory()->create([
            'nama' => $nama,
            'kota' => $kota,
            'is_active' => $aktif,
        ]);

        foreach ($kamar as $nomor => [$status, $harga]) {
            Kamar::factory()->create([
                'kos_id' => $kos->id,
                'nomor' => $nomor,
                'status' => $status,
                'harga_bulanan' => $harga,
            ]);
        }

        return $kos;
    }

    public function test_homepage_builds_map_data_and_lists_unknown_cities(): void
    {
        $this->buatKos('Kos Melati', 'Yogyakarta', ['A1' => ['kosong', 950000]]);
        $this->buatKos('Kos Anggrek', 'Yogyakarta', ['B1' => ['kosong', 1200000]]);
        $this->buatKos('Kos Kenanga', 'Jakarta', ['C1' => ['kosong', 800000]]);
        $this->buatKos('Kos Antah', 'Kota Fiktif', ['D1' => ['kosong', 700000]]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('jumlahKota', 3);
        $response->assertViewHas('kotaUtama', 'Indonesia');
        $response->assertViewHas('petaKota', function ($peta): bool {
            return $peta->count() === 2
                && $peta->first()['kota'] === 'Yogyakarta'
                && $peta->first()['jumlah_kos'] === 2
                && $peta->first()['jumlah_kosong'] === 2;
        });
        $response->assertViewHas('kotaTanpaPeta', fn ($kota) => $kota->contains('Kota Fiktif'));
    }

    public function test_homepage_hero_uses_real_data_instead_of_search_form(): void
    {
        $this->buatKos('Kos Melati', 'Yogyakarta', [
            'A6' => ['kosong', 1050000],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cari kamar.')
            ->assertSee('Satu tempat.')
            ->assertSee('Lihat kamar')
            ->assertSee('Untuk pengurus')
            ->assertSee('href="#kamar"', false)
            ->assertSee('href="#pengurus"', false)
            ->assertSee('Rp1,05 jt')
            ->assertSee('Rp1.050.000')
            ->assertDontSee('<input type="search"', false);
    }
}

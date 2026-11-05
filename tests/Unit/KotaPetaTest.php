<?php

namespace Tests\Unit;

use App\Models\Kos;
use App\Support\KotaPeta;
use PHPUnit\Framework\TestCase;

class KotaPetaTest extends TestCase
{
    public function test_maps_known_city_names_case_insensitively(): void
    {
        $this->assertSame(['x' => 41.5, 'y' => 80.0], KotaPeta::koordinat(' Yogyakarta '));
        $this->assertSame(['x' => 41.5, 'y' => 80.0], KotaPeta::koordinat('jogja'));
        $this->assertSame(['x' => 49.5, 'y' => 79.0], KotaPeta::koordinat('SURABAYA'));
    }

    public function test_returns_null_for_unknown_city(): void
    {
        $this->assertNull(KotaPeta::koordinat('Kota Fiktif'));
        $this->assertNull(KotaPeta::koordinat(null));
    }

    public function test_groups_cities_and_keeps_canonical_name(): void
    {
        $hasil = KotaPeta::rangkum(collect([
            $this->kos('Kos Melati', 'kos-melati', 'Jogja', 6, 2, 3, 950000, 1200000),
            $this->kos('Kos Anggrek', 'kos-anggrek', 'Yogyakarta', 6, 2, 3, 900000, 1100000),
            $this->kos('Kos Kenanga', 'kos-kenanga', 'Jakarta', 8, 2, 6, 1200000, 1800000),
            $this->kos('Kos Antah', 'kos-antah', 'Kota Fiktif', 4, 1, 3, 700000, 800000),
        ]));

        $this->assertCount(2, $hasil);
        $this->assertSame('Yogyakarta', $hasil[0]['kota']);
        $this->assertSame('yogyakarta', $hasil[0]['slug']);
        $this->assertSame(2, $hasil[0]['jumlah_kos']);
        $this->assertSame(12, $hasil[0]['jumlah_kamar']);
        $this->assertSame(4, $hasil[0]['jumlah_kosong']);
        $this->assertSame(6, $hasil[0]['jumlah_penghuni']);
        $this->assertSame(900000.0, $hasil[0]['harga_min']);
        $this->assertSame(1200000.0, $hasil[0]['harga_max']);
        $this->assertSame(2, count($hasil[0]['kos']));
    }

    public function test_keeps_null_price_range_when_city_has_no_available_room(): void
    {
        $hasil = KotaPeta::rangkum(collect([
            $this->kos('Kos Penuh', 'kos-penuh', 'Bandung', 6, 0, 6, null, null),
        ]));

        $this->assertCount(1, $hasil);
        $this->assertNull($hasil[0]['harga_min']);
        $this->assertNull($hasil[0]['harga_max']);
        $this->assertSame(0, $hasil[0]['jumlah_kosong']);
    }

    private function kos(
        string $nama,
        string $slug,
        string $kota,
        int $kamar,
        int $kosong,
        int $penghuni,
        ?float $hargaMin,
        ?float $hargaMax,
    ): Kos {
        $kos = new Kos(['nama' => $nama, 'slug' => $slug, 'kota' => $kota]);
        $kos->setAttribute('jumlah_kamar', $kamar);
        $kos->setAttribute('jumlah_kosong', $kosong);
        $kos->setAttribute('jumlah_penghuni', $penghuni);
        $kos->setAttribute('harga_min', $hargaMin);
        $kos->setAttribute('harga_max', $hargaMax);

        return $kos;
    }
}

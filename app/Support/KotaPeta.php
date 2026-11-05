<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class KotaPeta
{
    private const KOORDINAT = [
        'banda aceh' => ['x' => 10.0, 'y' => 22.0],
        'medan' => ['x' => 14.5, 'y' => 27.9],
        'padang' => ['x' => 20.0, 'y' => 60.0],
        'palembang' => ['x' => 28.0, 'y' => 63.0],
        'jakarta' => ['x' => 32.2, 'y' => 77.1],
        'bandung' => ['x' => 36.0, 'y' => 82.0],
        'yogyakarta' => ['x' => 42.8, 'y' => 79.4],
        'semarang' => ['x' => 45.0, 'y' => 80.0],
        'surabaya' => ['x' => 51.3, 'y' => 78.2],
        'malang' => ['x' => 49.0, 'y' => 81.5],
        'denpasar' => ['x' => 54.5, 'y' => 82.9],
        'pontianak' => ['x' => 38.0, 'y' => 58.0],
        'banjarmasin' => ['x' => 48.0, 'y' => 70.0],
        'balikpapan' => ['x' => 55.0, 'y' => 66.0],
        'makassar' => ['x' => 62.9, 'y' => 65.9],
        'manado' => ['x' => 65.0, 'y' => 48.0],
        'jayapura' => ['x' => 88.0, 'y' => 55.0],
    ];

    private const ALIAS = [
        'jogja' => 'yogyakarta',
        'jogjakarta' => 'yogyakarta',
        'jakarta raya' => 'jakarta',
        'dki jakarta' => 'jakarta',
        'denpasar bali' => 'denpasar',
    ];

    public static function normalisasi(?string $kota): string
    {
        if ($kota === null) {
            return '';
        }

        $normalisasi = strtolower(trim($kota));

        return self::ALIAS[$normalisasi] ?? $normalisasi;
    }

    public static function koordinat(?string $kota): ?array
    {
        $normalisasi = self::normalisasi($kota);

        if ($normalisasi === '') {
            return null;
        }

        return self::KOORDINAT[$normalisasi] ?? null;
    }

    public static function rangkum(Collection $daftarKos): Collection
    {
        $kosPerKota = $daftarKos
            ->filter(fn ($kos) => self::koordinat($kos->kota) !== null)
            ->groupBy(fn ($kos) => self::normalisasi($kos->kota));

        return $kosPerKota
            ->map(function (Collection $kosDiKota, string $slugKota) {
                $koordinat = self::koordinat($slugKota);
                $namaKanonik = Str::title($slugKota);

                $hargaMin = $kosDiKota->pluck('harga_min')->filter()->min();
                $hargaMax = $kosDiKota->pluck('harga_max')->filter()->max();

                $daftarKos = $kosDiKota->take(3)->map(fn ($kos) => [
                    'nama' => $kos->nama,
                    'slug' => $kos->slug,
                ])->values()->all();

                return [
                    'kota' => $namaKanonik,
                    'slug' => Str::slug($slugKota),
                    'x' => $koordinat['x'],
                    'y' => $koordinat['y'],
                    'jumlah_kos' => $kosDiKota->count(),
                    'jumlah_kamar' => (int) $kosDiKota->sum('jumlah_kamar'),
                    'jumlah_kosong' => (int) $kosDiKota->sum('jumlah_kosong'),
                    'jumlah_penghuni' => (int) $kosDiKota->sum('jumlah_penghuni'),
                    'harga_min' => $hargaMin !== null ? (float) $hargaMin : null,
                    'harga_max' => $hargaMax !== null ? (float) $hargaMax : null,
                    'kos' => $daftarKos,
                    'jumlah_kos_lainnya' => max(0, $kosDiKota->count() - 3),
                ];
            })
            ->sortBy('kota')
            ->sortByDesc('jumlah_kos')
            ->values();
    }
}

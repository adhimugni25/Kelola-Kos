<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class KotaPeta
{
    private const KOORDINAT = [
        'banda aceh' => ['x' => 2.3, 'y' => 2.8],
        'medan' => ['x' => 8.3, 'y' => 21.9],
        'padang' => ['x' => 9.1, 'y' => 47.6],
        'palembang' => ['x' => 20.7, 'y' => 51.0],
        'jakarta' => ['x' => 23.6, 'y' => 71.9],
        'bandung' => ['x' => 26.5, 'y' => 75.7],
        'yogyakarta' => ['x' => 33.2, 'y' => 81.3],
        'semarang' => ['x' => 31.8, 'y' => 74.0],
        'surabaya' => ['x' => 42.2, 'y' => 71.5],
        'malang' => ['x' => 41.9, 'y' => 78.8],
        'denpasar' => ['x' => 43.4, 'y' => 85.4],
        'pontianak' => ['x' => 25.2, 'y' => 30.9],
        'banjarmasin' => ['x' => 43.9, 'y' => 54.2],
        'balikpapan' => ['x' => 50.7, 'y' => 42.0],
        'makassar' => ['x' => 54.0, 'y' => 72.6],
        'manado' => ['x' => 67.8, 'y' => 10.8],
        'jayapura' => ['x' => 97.1, 'y' => 42.7],
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
                    'jumlah_kamar' => (int) $kos->jumlah_kamar,
                    'jumlah_kosong' => (int) $kos->jumlah_kosong,
                    'jumlah_penghuni' => (int) $kos->jumlah_penghuni,
                    'harga_min' => $kos->harga_min !== null ? (float) $kos->harga_min : null,
                    'harga_max' => $kos->harga_max !== null ? (float) $kos->harga_max : null,
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

<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class KotaPeta
{
    private const KOORDINAT = [
        'banda aceh' => ['x' => 9.5, 'y' => 24.0],
        'medan' => ['x' => 13.8, 'y' => 30.5],
        'padang' => ['x' => 18.5, 'y' => 56.0],
        'palembang' => ['x' => 24.5, 'y' => 64.5],
        'jakarta' => ['x' => 31.0, 'y' => 76.0],
        'bandung' => ['x' => 35.0, 'y' => 80.5],
        'yogyakarta' => ['x' => 41.5, 'y' => 80.0],
        'semarang' => ['x' => 44.0, 'y' => 79.0],
        'surabaya' => ['x' => 49.5, 'y' => 79.0],
        'malang' => ['x' => 48.0, 'y' => 82.5],
        'denpasar' => ['x' => 53.5, 'y' => 85.0],
        'pontianak' => ['x' => 36.0, 'y' => 58.5],
        'banjarmasin' => ['x' => 46.5, 'y' => 68.5],
        'balikpapan' => ['x' => 53.5, 'y' => 65.5],
        'makassar' => ['x' => 60.5, 'y' => 67.0],
        'manado' => ['x' => 64.0, 'y' => 49.5],
        'jayapura' => ['x' => 86.0, 'y' => 57.0],
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

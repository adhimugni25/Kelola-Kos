<?php

namespace App\Support;

final class Rupiah
{
    public static function penuh(float|int|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '—';
        }

        return 'Rp'.number_format((float) $nilai, 0, ',', '.');
    }

    public static function ringkas(float|int|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '—';
        }

        $angka = (float) $nilai;

        if ($angka >= 1_000_000) {
            return 'Rp'.rtrim(rtrim(number_format($angka / 1_000_000, 2, ',', '.'), '0'), ',').' jt';
        }

        if ($angka >= 1_000) {
            return 'Rp'.number_format($angka / 1_000, 0, ',', '.').' rb';
        }

        return self::penuh($angka);
    }
}

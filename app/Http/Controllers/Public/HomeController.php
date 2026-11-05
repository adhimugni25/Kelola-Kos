<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $kosAktif = Kos::query()
            ->where('is_active', true)
            ->withCount([
                'kamar as jumlah_kamar' => fn (Builder $q) => $q->withoutGlobalScope('kos_id'),
                'kamar as jumlah_terisi' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'terisi'),
                'kamar as jumlah_perbaikan' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'perbaikan'),
                'kamar as jumlah_kosong' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong'),
                'pemesanan as jumlah_penghuni' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'aktif'),
            ])
            ->withMin(['kamar as harga_min' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong')], 'harga_bulanan')
            ->withMax(['kamar as harga_max' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong')], 'harga_bulanan')
            ->get();

        $ringkasanKamar = [
            'terisi' => (int) $kosAktif->sum('jumlah_terisi'),
            'perbaikan' => (int) $kosAktif->sum('jumlah_perbaikan'),
            'kosong' => (int) $kosAktif->sum('jumlah_kosong'),
        ];

        $jumlahKota = $kosAktif->pluck('kota')->filter()->unique()->count();
        $jumlahKos = $kosAktif->count();
        $jumlahKamar = (int) $kosAktif->sum('jumlah_kamar');
        $jumlahKosong = (int) $kosAktif->sum('jumlah_kosong');
        $hargaMin = $kosAktif->pluck('harga_min')->filter()->min();
        $hargaMax = $kosAktif->pluck('harga_max')->filter()->max();
        $hargaMin = $hargaMin !== null ? (float) $hargaMin : null;
        $hargaMax = $hargaMax !== null ? (float) $hargaMax : null;

        $kotaUtama = $jumlahKota === 1
            ? ($kosAktif->first()?->kota ?? 'Indonesia')
            : 'Indonesia';

        $kamarKosong = Kamar::withoutGlobalScope('kos_id')
            ->where('status', 'kosong')
            ->whereHas('kos', fn (Builder $q) => $q->where('is_active', true))
            ->with('kos')
            ->orderBy('harga_bulanan')
            ->orderBy('id')
            ->limit(3)
            ->get();

        $petaKota = \App\Support\KotaPeta::rangkum($kosAktif);

        $kotaTanpaPeta = $kosAktif
            ->pluck('kota')
            ->filter()
            ->reject(fn (string $kota): bool => \App\Support\KotaPeta::koordinat($kota) !== null)
            ->unique(fn (string $kota): string => \App\Support\KotaPeta::normalisasi($kota))
            ->sort()
            ->values();

        return view('public.landing', [
            'jumlahKota' => $jumlahKota,
            'jumlahKos' => $jumlahKos,
            'jumlahKamar' => $jumlahKamar,
            'jumlahKosong' => $jumlahKosong,
            'ringkasanKamar' => $ringkasanKamar,
            'hargaMin' => $hargaMin,
            'hargaMax' => $hargaMax,
            'kamarKosong' => $kamarKosong,
            'kotaUtama' => $kotaUtama,
            'petaKota' => $petaKota,
            'kotaTanpaPeta' => $kotaTanpaPeta,
        ]);
    }
}

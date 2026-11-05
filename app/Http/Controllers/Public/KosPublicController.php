<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use Illuminate\View\View;

class KosPublicController extends Controller
{
    public function show(string $slug): View
    {
        $kos = Kos::where('slug', $slug)->firstOrFail();

        $kamarKosong = $kos->kamar()
            ->where('status', 'kosong')
            ->orderBy('nomor')
            ->get();

        return view('public.kos', [
            'kos' => $kos,
            'kamarKosong' => $kamarKosong,
            'menerimaPemesanan' => $kos->is_active && $kamarKosong->isNotEmpty(),
        ]);
    }
}

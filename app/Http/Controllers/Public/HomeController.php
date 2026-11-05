<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.landing', [
            'jumlahKos' => Kos::where('is_active', true)->count(),
        ]);
    }
}

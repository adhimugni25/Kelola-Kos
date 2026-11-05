<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('superadmin.dashboard', [
            'totalKos' => Kos::count(),
            'totalKosAktif' => Kos::where('is_active', true)->count(),
            'totalKamar' => Kamar::withoutGlobalScopes()->count(),
            'totalPengurus' => User::where('role', 'pengurus')->count(),
            'totalPenghuni' => User::where('role', 'penghuni')->count(),
        ]);
    }
}

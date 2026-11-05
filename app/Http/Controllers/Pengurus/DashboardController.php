<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        return view('pengurus.dashboard', [
            'ringkasan' => $this->reports->ringkasanDashboard($request->user()->kos_id),
        ]);
    }
}

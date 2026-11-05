<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penghuni\StorePemesananRequest;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PemesananController extends Controller
{
    public function __construct(private readonly BookingService $booking) {}

    public function index(): View
    {
        return view('penghuni.pemesanan.index', [
            'daftarPemesanan' => Pemesanan::with(['kamar', 'kos'])
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $kamar = Kamar::withoutGlobalScopes()
            ->with('kos')
            ->where('status', 'kosong')
            ->findOrFail($request->integer('kamar'));

        return view('penghuni.pemesanan.create', ['kamar' => $kamar]);
    }

    public function store(StorePemesananRequest $request): RedirectResponse
    {
        $this->booking->buat($request->user(), [
            'kamar_id' => $request->integer('kamar_id'),
            'tanggal_mulai' => $request->string('tanggal_mulai')->toString(),
            'durasi_bulan' => $request->integer('durasi_bulan'),
            'catatan' => $request->input('catatan'),
        ]);

        return redirect()->route('penghuni.pemesanan.index')
            ->with('status', 'Pengajuan sewa terkirim. Tunggu persetujuan pengurus.');
    }

    public function batal(Pemesanan $pemesanan): RedirectResponse
    {
        abort_unless($pemesanan->user_id === auth()->id(), 403);

        try {
            $this->booking->batalkan($pemesanan, 'Dibatalkan oleh penghuni.');
        } catch (RuntimeException $exception) {
            return redirect()->route('penghuni.pemesanan.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('penghuni.pemesanan.index')->with('status', 'Pengajuan dibatalkan.');
    }
}

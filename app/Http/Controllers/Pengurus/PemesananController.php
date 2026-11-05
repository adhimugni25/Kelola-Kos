<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\ApprovePemesananRequest;
use App\Http\Requests\Pengurus\RejectPemesananRequest;
use App\Models\Pemesanan;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class PemesananController extends Controller
{
    public function __construct(private readonly BookingService $booking) {}

    public function index(): View
    {
        return view('pengurus.pemesanan.index', [
            'daftarPemesanan' => Pemesanan::with(['kamar', 'user'])
                ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 WHEN status = 'aktif' THEN 1 ELSE 2 END")
                ->latest()
                ->get(),
        ]);
    }

    public function show(Pemesanan $pemesanan): View
    {
        $pemesanan->load(['kamar', 'user', 'penyetuju', 'tagihan']);

        return view('pengurus.pemesanan.show', ['pemesanan' => $pemesanan]);
    }

    public function setujui(ApprovePemesananRequest $request, Pemesanan $pemesanan): RedirectResponse
    {
        try {
            $this->booking->setujui(
                $pemesanan,
                $request->user(),
                $request->string('tanggal_mulai')->toString(),
                $request->integer('durasi_bulan'),
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.pemesanan.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.pemesanan.index')->with('status', 'Pengajuan disetujui dan tagihan pertama dibuat.');
    }

    public function tolak(RejectPemesananRequest $request, Pemesanan $pemesanan): RedirectResponse
    {
        try {
            $this->booking->tolak($pemesanan, $request->string('alasan_penolakan')->toString());
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.pemesanan.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.pemesanan.index')->with('status', 'Pengajuan ditolak.');
    }

    public function selesai(Pemesanan $pemesanan): RedirectResponse
    {
        try {
            $this->booking->selesaikan($pemesanan);
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.pemesanan.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.pemesanan.index')->with('status', 'Masa sewa selesai. Kamar kembali kosong.');
    }
}

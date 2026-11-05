<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penghuni\UploadBuktiRequest;
use App\Models\MetodePembayaran;
use App\Models\Tagihan;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;

class TagihanController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(): View
    {
        return view('penghuni.tagihan.index', [
            'daftarTagihan' => Tagihan::with(['pemesanan.kamar', 'pemesanan.kos', 'pembayaran'])
                ->whereHas('pemesanan', fn ($query) => $query->where('user_id', auth()->id()))
                ->latest()
                ->get(),
        ]);
    }

    public function show(Tagihan $tagihan): View
    {
        Gate::authorize('view', $tagihan);

        $tagihan->load(['pemesanan.kamar', 'pemesanan.kos', 'pembayaran.metodePembayaran']);

        return view('penghuni.tagihan.show', [
            'tagihan' => $tagihan,
            'metodePembayaran' => MetodePembayaran::withoutGlobalScopes()
                ->where('kos_id', $tagihan->kos_id)
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function bayar(UploadBuktiRequest $request, Tagihan $tagihan): RedirectResponse
    {
        try {
            $this->payments->unggah(
                $tagihan,
                $request->integer('metode_pembayaran_id'),
                (string) $request->input('jumlah'),
                $request->file('bukti'),
                $request->input('catatan_penghuni'),
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return redirect()->route('penghuni.tagihan.show', $tagihan)
            ->with('status', 'Bukti pembayaran terkirim. Tunggu verifikasi pengurus.');
    }
}

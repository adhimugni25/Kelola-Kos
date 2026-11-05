<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\RejectPembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PembayaranController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        return view('pengurus.pembayaran.index', [
            'daftarPembayaran' => Pembayaran::with([
                'tagihan.pemesanan.kamar',
                'tagihan.pemesanan.user',
                'metodePembayaran',
            ])
                ->whereHas('tagihan', fn ($query) => $query->where('kos_id', $request->user()->kos_id))
                ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
                ->latest()
                ->get(),
        ]);
    }

    public function terima(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $this->pastikanMilikKos($request->user(), $pembayaran);

        try {
            $this->payments->terima($pembayaran, $request->user());
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.pembayaran.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.pembayaran.index')->with('status', 'Pembayaran diterima. Tagihan lunas.');
    }

    public function tolak(RejectPembayaranRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $this->pastikanMilikKos($request->user(), $pembayaran);

        try {
            $this->payments->tolak(
                $pembayaran,
                $request->user(),
                $request->string('catatan_verifikasi')->toString(),
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.pembayaran.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.pembayaran.index')->with('status', 'Pembayaran ditolak.');
    }

    private function pastikanMilikKos(User $pengurus, Pembayaran $pembayaran): void
    {
        $kosId = Tagihan::withoutGlobalScopes()
            ->whereKey($pembayaran->tagihan_id)
            ->value('kos_id');

        abort_unless($kosId !== null && $kosId === $pengurus->kos_id, 404);
    }
}

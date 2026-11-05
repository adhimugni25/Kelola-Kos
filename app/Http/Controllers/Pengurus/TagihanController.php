<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\StoreTagihanRequest;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class TagihanController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function index(): View
    {
        return view('pengurus.tagihan.index', [
            'daftarTagihan' => Tagihan::with(['pemesanan.kamar', 'pemesanan.user', 'pembayaran'])
                ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 WHEN status = 'belum_bayar' THEN 1 ELSE 2 END")
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('pengurus.tagihan.create', [
            'daftarPemesanan' => Pemesanan::with(['kamar', 'user'])
                ->where('status', 'aktif')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(StoreTagihanRequest $request): RedirectResponse
    {
        $nominalSewa = (float) $request->input('nominal_sewa');
        $biayaTambahan = (float) $request->input('biaya_tambahan', 0);

        Tagihan::create([
            'kos_id' => $request->user()->kos_id,
            'pemesanan_id' => $request->integer('pemesanan_id'),
            'periode_bulan' => $request->integer('periode_bulan'),
            'periode_tahun' => $request->integer('periode_tahun'),
            'nominal_sewa' => $nominalSewa,
            'biaya_tambahan' => $biayaTambahan,
            'keterangan_tambahan' => $request->input('keterangan_tambahan'),
            'total' => $nominalSewa + $biayaTambahan,
            'jatuh_tempo' => $request->string('jatuh_tempo')->toString(),
            'status' => 'belum_bayar',
        ]);

        return redirect()->route('pengurus.tagihan.index')->with('status', 'Tagihan berhasil dibuat.');
    }

    public function bulanIni(Request $request): RedirectResponse
    {
        $jumlah = $this->billing->buatTagihanBulanIni($request->user()->kos_id);

        return redirect()->route('pengurus.tagihan.index')->with(
            'status',
            $jumlah === 0
                ? 'Semua tagihan periode ini sudah ada.'
                : "{$jumlah} tagihan periode ini berhasil dibuat."
        );
    }

    public function edit(Tagihan $tagihan): View
    {
        return view('pengurus.tagihan.edit', ['tagihan' => $tagihan]);
    }

    public function update(StoreTagihanRequest $request, Tagihan $tagihan): RedirectResponse
    {
        if ($tagihan->status !== 'belum_bayar') {
            return redirect()->route('pengurus.tagihan.index')
                ->with('error', 'Tagihan yang sudah diproses tidak bisa diubah.');
        }

        $nominalSewa = (float) $request->input('nominal_sewa');
        $biayaTambahan = (float) $request->input('biaya_tambahan', 0);

        $tagihan->update([
            'nominal_sewa' => $nominalSewa,
            'biaya_tambahan' => $biayaTambahan,
            'keterangan_tambahan' => $request->input('keterangan_tambahan'),
            'total' => $nominalSewa + $biayaTambahan,
            'jatuh_tempo' => Carbon::parse($request->string('jatuh_tempo')->toString())->toDateString(),
        ]);

        return redirect()->route('pengurus.tagihan.index')->with('status', 'Tagihan berhasil diperbarui.');
    }

    public function batal(Tagihan $tagihan): RedirectResponse
    {
        try {
            $this->billing->batalkan($tagihan);
        } catch (RuntimeException $exception) {
            return redirect()->route('pengurus.tagihan.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('pengurus.tagihan.index')->with('status', 'Tagihan dibatalkan.');
    }
}

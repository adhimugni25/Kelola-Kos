<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\StoreMetodePembayaranRequest;
use App\Models\MetodePembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MetodePembayaranController extends Controller
{
    public function index(): View
    {
        return view('pengurus.metode-pembayaran.index', [
            'daftarMetode' => MetodePembayaran::orderBy('nama')->get(),
        ]);
    }

    public function store(StoreMetodePembayaranRequest $request): RedirectResponse
    {
        MetodePembayaran::create([
            'kos_id' => $request->user()->kos_id,
            'tipe' => $request->string('tipe')->toString(),
            'nama' => $request->string('nama')->toString(),
            'nomor' => $request->string('nomor')->toString(),
            'atas_nama' => $request->string('atas_nama')->toString(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('pengurus.metode-pembayaran.index')
            ->with('status', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function toggle(MetodePembayaran $metodePembayaran): RedirectResponse
    {
        $metodePembayaran->update(['is_active' => ! $metodePembayaran->is_active]);

        return redirect()->route('pengurus.metode-pembayaran.index')
            ->with('status', 'Status metode pembayaran diperbarui.');
    }

    public function destroy(MetodePembayaran $metodePembayaran): RedirectResponse
    {
        if ($metodePembayaran->pembayaran()->exists()) {
            return redirect()->route('pengurus.metode-pembayaran.index')
                ->with('error', 'Metode ini sudah dipakai pada pembayaran, nonaktifkan saja.');
        }

        $metodePembayaran->delete();

        return redirect()->route('pengurus.metode-pembayaran.index')
            ->with('status', 'Metode pembayaran dihapus.');
    }
}

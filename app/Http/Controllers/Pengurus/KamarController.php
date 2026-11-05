<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\StoreKamarRequest;
use App\Http\Requests\Pengurus\UpdateKamarRequest;
use App\Models\Kamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KamarController extends Controller
{
    public function index(): View
    {
        return view('pengurus.kamar.index', [
            'daftarKamar' => Kamar::orderBy('nomor')->get(),
        ]);
    }

    public function create(): View
    {
        return view('pengurus.kamar.create');
    }

    public function store(StoreKamarRequest $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['kos_id'] = $request->user()->kos_id;
        $data['foto_path'] = $request->hasFile('foto')
            ? $request->file('foto')->store('kamar', 'public')
            : null;

        Kamar::create($data);

        return redirect()->route('pengurus.kamar.index')->with('status', 'Kamar berhasil ditambahkan.');
    }

    public function edit(Kamar $kamar): View
    {
        return view('pengurus.kamar.edit', ['kamar' => $kamar]);
    }

    public function update(UpdateKamarRequest $request, Kamar $kamar): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('foto')) {
            if ($kamar->foto_path !== null) {
                Storage::disk('public')->delete($kamar->foto_path);
            }

            $data['foto_path'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar->update($data);

        return redirect()->route('pengurus.kamar.index')->with('status', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar): RedirectResponse
    {
        $adaPemesananAktif = $kamar->pemesanan()
            ->whereIn('status', ['menunggu', 'aktif'])
            ->exists();

        if ($adaPemesananAktif) {
            return redirect()->route('pengurus.kamar.index')
                ->with('error', 'Kamar tidak bisa dihapus karena masih punya pemesanan menunggu atau aktif.');
        }

        if ($kamar->foto_path !== null) {
            Storage::disk('public')->delete($kamar->foto_path);
        }

        $kamar->delete();

        return redirect()->route('pengurus.kamar.index')->with('status', 'Kamar berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(StoreKamarRequest|UpdateKamarRequest $request): array
    {
        return [
            'nomor' => $request->string('nomor')->toString(),
            'tipe' => $request->string('tipe')->toString(),
            'harga_bulanan' => $request->input('harga_bulanan'),
            'fasilitas' => $request->input('fasilitas'),
            'catatan' => $request->input('catatan'),
            'status' => $request->string('status')->toString(),
        ];
    }
}

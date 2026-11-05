<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreKosRequest;
use App\Http\Requests\SuperAdmin\UpdateKosRequest;
use App\Models\Kos;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KosController extends Controller
{
    public function index(): View
    {
        return view('superadmin.kos.index', [
            'daftarKos' => Kos::withCount(['kamar', 'pengurus'])->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.kos.create');
    }

    public function store(StoreKosRequest $request): RedirectResponse
    {
        Kos::create([
            'nama' => $request->string('nama')->toString(),
            'slug' => $request->string('slug')->toString(),
            'alamat' => $request->string('alamat')->toString(),
            'kota' => $request->string('kota')->toString(),
            'deskripsi' => $request->input('deskripsi'),
            'kontak' => $request->string('kontak')->toString(),
            'fasilitas_umum' => $request->input('fasilitas_umum'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.kos.index')->with('status', 'Kos berhasil dibuat.');
    }

    public function edit(Kos $kos): View
    {
        return view('superadmin.kos.edit', ['kos' => $kos]);
    }

    public function update(UpdateKosRequest $request, Kos $kos): RedirectResponse
    {
        $kos->update([
            'nama' => $request->string('nama')->toString(),
            'slug' => $request->string('slug')->toString(),
            'alamat' => $request->string('alamat')->toString(),
            'kota' => $request->string('kota')->toString(),
            'deskripsi' => $request->input('deskripsi'),
            'kontak' => $request->string('kontak')->toString(),
            'fasilitas_umum' => $request->input('fasilitas_umum'),
        ]);

        return redirect()->route('admin.kos.index')->with('status', 'Kos berhasil diperbarui.');
    }

    public function toggle(Kos $kos): RedirectResponse
    {
        $kos->update(['is_active' => ! $kos->is_active]);

        return redirect()->route('admin.kos.index')->with('status', 'Status kos diperbarui.');
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\ResetPasswordRequest;
use App\Http\Requests\SuperAdmin\StorePengurusRequest;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengurusController extends Controller
{
    public function index(): View
    {
        return view('superadmin.pengurus.index', [
            'daftarPengurus' => User::with('kos')
                ->where('role', 'pengurus')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.pengurus.create', [
            'daftarKos' => Kos::orderBy('nama')->get(),
        ]);
    }

    public function store(StorePengurusRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->input('phone'),
            'password' => $request->string('password')->toString(),
            'role' => 'pengurus',
            'kos_id' => $request->integer('kos_id'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.pengurus.index')->with('status', 'Akun pengurus berhasil dibuat.');
    }

    public function reset(ResetPasswordRequest $request, User $pengurus): RedirectResponse
    {
        abort_unless($pengurus->isPengurus(), 404);

        $pengurus->update(['password' => $request->string('password')->toString()]);

        return redirect()->route('admin.pengurus.index')->with('status', 'Password pengurus diperbarui.');
    }

    public function toggle(User $pengurus): RedirectResponse
    {
        abort_unless($pengurus->isPengurus(), 404);

        $pengurus->update(['is_active' => ! $pengurus->is_active]);

        return redirect()->route('admin.pengurus.index')->with('status', 'Status akun diperbarui.');
    }
}

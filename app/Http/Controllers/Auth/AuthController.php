<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah, atau akun tidak aktif.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put('login_at', now()->timestamp);

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        return $this->redirectByRole($user->role);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => 'penghuni',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('login_at', now()->timestamp);

        return redirect()->route('penghuni.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectByRole(string $role): RedirectResponse
    {
        return match ($role) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'pengurus' => redirect()->route('pengurus.dashboard'),
            default => redirect()->route('penghuni.dashboard'),
        };
    }
}

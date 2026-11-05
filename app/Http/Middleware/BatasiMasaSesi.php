<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BatasiMasaSesi
{
    /**
     * Batas absolut 12 jam sejak login. Batas idle 30 menit ditangani
     * oleh konfigurasi SESSION_LIFETIME.
     */
    private const BATAS_ABSOLUT_DETIK = 12 * 60 * 60;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null) {
            $loginPada = (int) $request->session()->get('login_at', 0);

            if ($loginPada > 0 && now()->timestamp - $loginPada > self::BATAS_ABSOLUT_DETIK) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda berakhir. Silakan masuk kembali.');
            }
        }

        return $next($request);
    }
}

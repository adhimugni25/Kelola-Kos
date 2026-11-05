<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembayaranController extends Controller
{
    public function bukti(Pembayaran $pembayaran): StreamedResponse
    {
        Gate::authorize('view', $pembayaran);

        $disk = Storage::disk('bukti');

        abort_unless($disk->exists($pembayaran->bukti_path), 404);

        return $disk->response($pembayaran->bukti_path);
    }
}

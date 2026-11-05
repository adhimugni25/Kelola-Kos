<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        $tagihan = $pembayaran->tagihan;

        if ($tagihan === null) {
            return false;
        }

        if ($user->isPengurus()) {
            return $user->kos_id === $tagihan->kos_id;
        }

        if ($user->isPenghuni()) {
            return $tagihan->pemesanan()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}

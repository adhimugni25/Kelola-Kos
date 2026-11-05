<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;

class TagihanPolicy
{
    public function view(User $user, Tagihan $tagihan): bool
    {
        if ($user->isPengurus()) {
            return $user->kos_id === $tagihan->kos_id;
        }

        if ($user->isPenghuni()) {
            return $tagihan->pemesanan()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}

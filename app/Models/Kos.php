<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kos extends Model
{
    use HasFactory;

    protected $table = 'kos';

    protected $fillable = [
        'nama',
        'slug',
        'alamat',
        'kota',
        'deskripsi',
        'kontak',
        'fasilitas_umum',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }

    public function pengurus(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'pengurus');
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function metodePembayaran(): HasMany
    {
        return $this->hasMany(MetodePembayaran::class);
    }
}

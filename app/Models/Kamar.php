<?php

namespace App\Models;

use App\Support\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kamar extends Model
{
    use HasFactory;
    use TenantScope;

    protected $table = 'kamar';

    protected $fillable = [
        'kos_id',
        'nomor',
        'tipe',
        'harga_bulanan',
        'fasilitas',
        'foto_path',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'harga_bulanan' => 'decimal:2',
        ];
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }
}

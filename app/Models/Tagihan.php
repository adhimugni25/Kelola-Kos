<?php

namespace App\Models;

use App\Support\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory;
    use TenantScope;

    protected $table = 'tagihan';

    protected $fillable = [
        'kos_id',
        'pemesanan_id',
        'periode_bulan',
        'periode_tahun',
        'nominal_sewa',
        'biaya_tambahan',
        'keterangan_tambahan',
        'total',
        'jatuh_tempo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'periode_bulan' => 'integer',
            'periode_tahun' => 'integer',
            'nominal_sewa' => 'decimal:2',
            'biaya_tambahan' => 'decimal:2',
            'total' => 'decimal:2',
            'jatuh_tempo' => 'date',
        ];
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}

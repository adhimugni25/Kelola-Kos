<?php

namespace App\Models;

use App\Support\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemesanan extends Model
{
    use HasFactory;
    use TenantScope;

    protected $table = 'pemesanan';

    protected $fillable = [
        'kos_id',
        'kamar_id',
        'user_id',
        'tanggal_mulai',
        'durasi_bulan',
        'harga_bulanan',
        'berakhir_pada',
        'status',
        'catatan',
        'alasan_penolakan',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'berakhir_pada' => 'date',
            'disetujui_pada' => 'datetime',
            'harga_bulanan' => 'decimal:2',
            'durasi_bulan' => 'integer',
        ];
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
}

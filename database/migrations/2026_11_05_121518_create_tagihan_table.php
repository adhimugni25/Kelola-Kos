<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->restrictOnDelete();
            $table->tinyInteger('periode_bulan');
            $table->smallInteger('periode_tahun');
            $table->decimal('nominal_sewa', 12, 2);
            $table->decimal('biaya_tambahan', 12, 2)->default(0);
            $table->string('keterangan_tambahan')->nullable();
            $table->decimal('total', 12, 2);
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'dibatalkan'])->default('belum_bayar');
            $table->timestamps();

            $table->unique(['pemesanan_id', 'periode_tahun', 'periode_bulan']);
            $table->index(['kos_id', 'status']);
            $table->index('jatuh_tempo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};

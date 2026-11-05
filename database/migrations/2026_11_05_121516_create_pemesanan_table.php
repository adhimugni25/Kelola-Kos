<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_mulai');
            $table->smallInteger('durasi_bulan');
            $table->decimal('harga_bulanan', 12, 2)->nullable();
            $table->date('berakhir_pada')->nullable();
            $table->enum('status', ['menunggu', 'aktif', 'ditolak', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();

            $table->index(['kos_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['kamar_id', 'status']);
            $table->index(['kos_id', 'tanggal_mulai']);
            $table->index(['kos_id', 'berakhir_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete();
            $table->foreignId('metode_pembayaran_id')->constrained('metode_pembayaran')->restrictOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti_path');
            $table->text('catatan_penghuni')->nullable();
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index(['tagihan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};

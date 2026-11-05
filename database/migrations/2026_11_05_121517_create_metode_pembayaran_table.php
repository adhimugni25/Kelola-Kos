<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metode_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
            $table->enum('tipe', ['bank', 'ewallet']);
            $table->string('nama');
            $table->string('nomor');
            $table->string('atas_nama');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kos_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metode_pembayaran');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
            $table->string('nomor');
            $table->string('tipe');
            $table->decimal('harga_bulanan', 12, 2);
            $table->text('fasilitas')->nullable();
            $table->string('foto_path')->nullable();
            $table->enum('status', ['kosong', 'terisi', 'perbaikan'])->default('kosong');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['kos_id', 'nomor']);
            $table->index(['kos_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('kos_id')->nullable()->constrained('kos')->restrictOnDelete();
            $table->string('aksi');
            $table->string('entitas')->nullable();
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->json('detail')->nullable();
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['kos_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['aksi', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

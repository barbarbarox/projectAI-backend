<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulasi_serangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->string('nama_skenario');
            $table->enum('profil_penyerang', [
                'script_kiddie', 'penyerang_oportunistik',
                'penyerang_tertarget', 'ancaman_internal'
            ])->nullable();
            $table->text('narasi_teknis')->nullable();
            $table->text('narasi_eksekutif')->nullable();
            $table->float('skor_kemungkinan')->nullable();
            $table->float('skor_dampak')->nullable();
            $table->json('rantai_serangan')->nullable();
            $table->json('fase_attck')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulasi_serangan');
    }
};

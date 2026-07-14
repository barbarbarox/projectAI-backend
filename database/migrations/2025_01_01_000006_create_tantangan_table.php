<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tantangan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['temukan_bug', 'perbaiki_kode', 'pilihan_ganda']);
            $table->string('kategori')->nullable();
            $table->enum('bahasa_pemrograman', ['php', 'javascript', 'python', 'java', 'umum'])->nullable();
            $table->text('kode_soal')->nullable();
            $table->text('jawaban_benar');
            $table->json('pilihan_jawaban')->nullable();
            $table->text('penjelasan')->nullable();
            $table->string('referensi_cwe', 20)->nullable();
            $table->string('referensi_owasp')->nullable();
            $table->unsignedTinyInteger('poin')->default(10);
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit']);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tantangan');
    }
};

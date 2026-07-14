<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->enum('tipe', [
                'kerentanan', 'miskonfigurasi', 'eksposur_data',
                'ketergantungan_rentan', 'kriptografi_lemah',
                'injeksi', 'autentikasi_lemah', 'otorisasi_lemah', 'lainnya'
            ])->nullable();
            $table->enum('tingkat_keparahan', ['kritis', 'tinggi', 'sedang', 'rendah', 'info']);
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('nomor_baris')->nullable();
            $table->text('kode_rentan')->nullable();
            $table->text('kode_aman')->nullable();
            $table->string('cve_id', 30)->nullable();
            $table->string('cwe_id', 20)->nullable();
            $table->string('capec_id', 20)->nullable();
            $table->string('teknik_attck', 20)->nullable();
            $table->text('tautan_referensi')->nullable();
            $table->text('remediasi')->nullable();
            $table->unsignedTinyInteger('prioritas_perbaikan')->nullable();
            $table->enum('estimasi_usaha', ['mudah', 'sedang', 'sulit'])->nullable();
            $table->enum('tingkat_kepercayaan', ['tinggi', 'sedang', 'rendah'])->nullable();
            $table->boolean('is_disensor')->default(false);
            $table->timestamps();

            $table->index('scan_id');
            $table->index('tingkat_keparahan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temuan');
    }
};

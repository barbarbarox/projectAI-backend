<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('tipe_scan', ['url', 'zip', 'kode']);
            $table->text('target')->nullable();
            $table->string('nama_file')->nullable();
            $table->enum('status', ['memproses', 'selesai', 'gagal'])->default('memproses');
            $table->unsignedTinyInteger('skor_keamanan')->nullable();
            $table->enum('verdict', ['aman', 'perhatian', 'berbahaya'])->nullable();
            $table->enum('verdict_deploy', ['aman_deploy', 'deploy_risiko', 'tidak_aman_deploy'])->nullable();
            $table->text('ringkasan_eksekutif')->nullable();
            $table->text('ringkasan_teknis')->nullable();
            $table->json('teknologi_terdeteksi')->nullable();
            $table->float('confidence_score')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamp('dihapus_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};

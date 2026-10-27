<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tantangan', function (Blueprint $table) {
            // Format presentasi soal: quiz biasa, kode, skenario, ciphertext, file
            $table->string('format_soal', 30)->default('quiz')->after('tipe');
            // Konten tambahan tergantung format: kode snippet, ciphertext, file path, daftar poin skenario, dll
            $table->json('konten_tambahan')->nullable()->after('kode_soal');
            // Bahasa kode (untuk format kode)
            $table->string('bahasa_kode', 30)->nullable()->after('konten_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('tantangan', function (Blueprint $table) {
            $table->dropColumn(['format_soal', 'konten_tambahan', 'bahasa_kode']);
        });
    }
};

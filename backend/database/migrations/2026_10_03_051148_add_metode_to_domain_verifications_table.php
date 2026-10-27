<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            // Metode verifikasi: file_upload (default), dns_txt, meta_tag, email_domain
            $table->enum('metode_verifikasi', ['file_upload', 'dns_txt', 'meta_tag', 'email_domain'])
                  ->default('file_upload')
                  ->after('nama_file');

            // Untuk email_domain: simpan email tujuan & kode verifikasi yang dikirim
            $table->string('email_verifikasi', 255)->nullable()->after('metode_verifikasi');
            $table->string('kode_email', 12)->nullable()->after('email_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            $table->dropColumn(['metode_verifikasi', 'email_verifikasi', 'kode_email']);
        });
    }
};

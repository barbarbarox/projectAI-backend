<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign keys terlebih dahulu
        Schema::table('temuan', function (Blueprint $table) {
            $table->dropForeign(['scan_id']);
        });

        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->dropForeign(['scan_id']);
        });

        // 2. Ubah kolom id di tabel scans dari BIGINT ke UUID (CHAR(36))
        Schema::table('scans', function (Blueprint $table) {
            $table->char('id', 36)->change();
        });

        // 3. Ubah kolom id dan scan_id di tabel temuan
        Schema::table('temuan', function (Blueprint $table) {
            $table->char('id', 36)->change();
            $table->char('scan_id', 36)->change();
        });

        // 4. Ubah kolom id dan scan_id di tabel simulasi_serangan
        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->char('id', 36)->change();
            $table->char('scan_id', 36)->change();
        });

        // 5. Re-apply foreign keys
        Schema::table('temuan', function (Blueprint $table) {
            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();
        });

        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Drop foreign keys
        Schema::table('temuan', function (Blueprint $table) {
            $table->dropForeign(['scan_id']);
        });

        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->dropForeign(['scan_id']);
        });

        // Revert ke bigint auto increment
        Schema::table('scans', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
        });

        Schema::table('temuan', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
            $table->unsignedBigInteger('scan_id')->change();
        });

        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->change();
            $table->unsignedBigInteger('scan_id')->change();
        });

        // Re-apply foreign keys
        Schema::table('temuan', function (Blueprint $table) {
            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();
        });

        Schema::table('simulasi_serangan', function (Blueprint $table) {
            $table->foreign('scan_id')->references('id')->on('scans')->cascadeOnDelete();
        });
    }
};

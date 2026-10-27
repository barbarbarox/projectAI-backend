<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Asset Groups
        Schema::create('asset_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->string('warna', 20)->default('indigo'); // indigo, cyan, emerald, amber, purple, rose
            $table->string('environment', 30)->default('production'); // production, staging, development, internal
            $table->timestamps();
        });

        // 2. Tabel Target Aset di dalam Grup
        Schema::create('asset_group_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_group_id')->constrained('asset_groups')->cascadeOnDelete();
            $table->string('target_url', 255);
            $table->string('label', 100)->nullable();
            $table->char('last_scan_id', 36)->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->integer('last_skor_keamanan')->nullable();
            $table->string('last_verdict', 30)->nullable();
            $table->timestamps();

            $table->foreign('last_scan_id')->references('id')->on('scans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_group_targets');
        Schema::dropIfExists('asset_groups');
    }
};

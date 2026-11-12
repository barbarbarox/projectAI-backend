<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained('website_monitors')->cascadeOnDelete();
            $table->timestamp('snapshot_at');

            // Elemen kunci halaman
            $table->string('content_hash', 64)->nullable();    // SHA-256 body HTML
            $table->string('title', 1000)->nullable();         // Konten <title>
            $table->json('h1_texts')->nullable();              // Array semua <h1>
            $table->string('canonical_url', 2048)->nullable(); // <link rel="canonical">
            $table->string('meta_description', 1000)->nullable();
            $table->string('link_hash', 64)->nullable();       // Hash semua <a href> eksternal

            // Elemen keamanan kritis
            $table->json('external_scripts')->nullable();      // Array {src, hash} script eksternal
            $table->json('external_iframes')->nullable();      // Array {src} iframe
            $table->json('inline_scripts_hash')->nullable();   // Hash tiap inline script

            // Visual
            $table->string('screenshot_path', 500)->nullable();

            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['monitor_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_baselines');
    }
};

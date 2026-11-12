<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_url', 2048);
            $table->string('nama', 255)->nullable(); // Label dari user
            $table->enum('status', ['active', 'paused', 'error'])->default('active');
            $table->smallInteger('interval_menit')->default(60); // Min 15
            $table->boolean('is_dynamic_content')->default(false); // Konten dinamis (berita, e-commerce)
            $table->tinyInteger('threshold_pct')->default(10); // Toleransi perubahan 0-100%
            $table->boolean('alert_wa')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->integer('consecutive_errors')->default(0); // Jumlah error berturut-turut
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('next_check_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_monitors');
    }
};

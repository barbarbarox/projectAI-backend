<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_integrity_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->nullable()->constrained('website_monitors')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('check_type', ['quick_check', 'scheduled'])->default('scheduled');
            $table->string('target_url', 2048); // Simpan URL langsung agar tetap ada walau monitor dihapus
            $table->timestamp('detected_at');

            $table->enum('alert_type', [
                'clean',
                'content_change',
                'script_inject',
                'iframe_inject',
                'defacement',
                'redirect_change',
                'visual_change',
                'seo_spam',
            ])->default('clean');
            $table->enum('severity', ['info', 'warning', 'high', 'critical'])->default('info');
            $table->tinyInteger('diff_score')->default(0); // 0-100

            $table->json('evidence')->nullable();     // Semua sinyal terdeteksi
            $table->text('ai_analysis')->nullable();  // Output Gemini
            $table->tinyInteger('ai_risk_score')->nullable(); // 0-100

            // Status
            $table->boolean('is_read')->default(false);
            $table->boolean('is_acknowledged')->default(false); // User mengakui perubahan wajar
            $table->timestamp('notif_wa_sent_at')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'is_read']);
            $table->index(['monitor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_integrity_alerts');
    }
};

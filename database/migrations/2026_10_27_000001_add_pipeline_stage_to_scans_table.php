<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            // Pipeline stage tracking (1–6 = tahap aktif, 0 = belum mulai)
            $table->unsignedTinyInteger('pipeline_stage')->default(0)->after('progress_persen');
            // Tech stack terstruktur (JSON)
            $table->json('tech_stack')->nullable()->after('teknologi_terdeteksi');
            // Screenshot URL/path
            $table->string('screenshot_path', 500)->nullable()->after('tech_stack');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['pipeline_stage', 'tech_stack', 'screenshot_path']);
        });
    }
};

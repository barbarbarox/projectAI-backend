<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->string('mode_ai', 20)->default('dengan_ai')->after('mode_scan');  // dengan_ai | tanpa_ai
            $table->string('mode_rag', 20)->default('dengan_rag')->after('mode_ai');   // dengan_rag | tanpa_rag
            $table->string('progress_step', 50)->nullable()->after('status');           // current step name
            $table->unsignedTinyInteger('progress_persen')->default(0)->after('progress_step'); // 0-100
            $table->json('rag_references')->nullable()->after('data_mentah');           // RAG source references
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['mode_ai', 'mode_rag', 'progress_step', 'progress_persen', 'rag_references']);
        });
    }
};

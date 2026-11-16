<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan', function (Blueprint $table) {
            // JSON nullable — diisi saat user klik "Generate AI Fix", lalu di-cache
            $table->json('ai_remediasi')->nullable()->after('remediasi');
        });
    }

    public function down(): void
    {
        Schema::table('temuan', function (Blueprint $table) {
            $table->dropColumn('ai_remediasi');
        });
    }
};

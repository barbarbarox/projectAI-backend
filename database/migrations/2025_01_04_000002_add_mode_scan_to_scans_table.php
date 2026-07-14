<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->string('mode_scan', 20)->default('biasa')->after('tipe_scan');
            $table->boolean('is_verified_domain')->default(false)->after('mode_scan');
            $table->json('data_mentah')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['mode_scan', 'is_verified_domain', 'data_mentah']);
        });
    }
};

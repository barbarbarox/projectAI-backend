<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap')->nullable()->after('name');
            $table->unsignedTinyInteger('scan_count_today')->default(0)->after('remember_token');
            $table->date('last_scan_reset')->nullable()->after('scan_count_today');
            $table->enum('tier', ['gratis', 'pro', 'admin'])->default('gratis')->after('last_scan_reset');
            $table->boolean('is_verified')->default(false)->after('tier');
            $table->timestamp('last_login')->nullable()->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nama_lengkap', 'scan_count_today', 'last_scan_reset',
                'tier', 'is_verified', 'last_login'
            ]);
        });
    }
};

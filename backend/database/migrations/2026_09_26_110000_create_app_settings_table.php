<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        \App\Models\AppSetting::create([
            'key' => 'wa_sender_number',
            'value' => '',
            'description' => 'Nomor WhatsApp pengirim (format: 628xxxxxxxxxx)',
        ]);

        \App\Models\AppSetting::create([
            'key' => 'openwa_base_url',
            'value' => env('OPENWA_BASE_URL', 'http://localhost:3000'),
            'description' => 'URL server OpenWA API Gateway',
        ]);

        \App\Models\AppSetting::create([
            'key' => 'openwa_api_key',
            'value' => '',
            'description' => 'API Key untuk OpenWA Gateway',
        ]);

        \App\Models\AppSetting::create([
            'key' => 'openwa_session_id',
            'value' => 'default',
            'description' => 'Session ID sesi WhatsApp di OpenWA',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

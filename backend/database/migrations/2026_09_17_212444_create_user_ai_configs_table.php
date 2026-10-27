<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ai_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Metode koneksi: api_key | oauth_google
            $table->string('method', 20)->default('api_key');

            // Provider & Model
            $table->string('provider', 50); // google, openai, anthropic, nvidia, groq, openrouter
            $table->string('label', 100)->nullable();

            // API Key (encrypted via model cast)
            $table->text('api_key')->nullable();

            // OAuth tokens (encrypted via model cast)
            $table->text('oauth_access_token')->nullable();
            $table->text('oauth_refresh_token')->nullable();
            $table->timestamp('oauth_expires_at')->nullable();

            // Model selection
            $table->json('available_models')->nullable();
            $table->string('selected_model', 150);

            // Status & Usage
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('total_requests')->default(0);

            $table->timestamps();

            // Satu user hanya boleh punya 1 config aktif
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ai_configs');
    }
};

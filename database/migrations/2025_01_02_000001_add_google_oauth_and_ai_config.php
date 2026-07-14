<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('google_id');
        });

        Schema::create('ai_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('provider');         // google, openai, anthropic, etc
            $table->string('label');            // Display name
            $table->text('api_key');
            $table->string('detected_provider')->nullable();
            $table->json('available_models')->nullable();
            $table->string('selected_model')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar']);
        });
        Schema::dropIfExists('ai_configurations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poin_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tantangan_id')->constrained('tantangan')->cascadeOnDelete();
            $table->unsignedTinyInteger('poin_diperoleh');
            $table->text('jawaban_user')->nullable();
            $table->boolean('is_benar');
            $table->timestamp('selesai_at')->nullable();
            $table->unique(['user_id', 'tantangan_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poin_user');
    }
};

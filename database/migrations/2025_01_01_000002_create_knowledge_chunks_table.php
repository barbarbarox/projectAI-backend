<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->json('embedding')->nullable(); // Embedding disimpan di Pinecone, ini hanya placeholder
            $table->string('source', 50)->index();
            $table->string('source_id', 100)->nullable()->index();
            $table->string('title', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('chunk_index')->default(0);
            $table->timestamps();
        });

        // PostgreSQL: Buat GIN index untuk full-text search (pengganti MySQL FULLTEXT)
        if (config('database.default') === 'pgsql') {
            DB::statement("CREATE INDEX knowledge_chunks_content_gin ON knowledge_chunks USING gin(to_tsvector('indonesian', content))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temuan_evidence', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('temuan_id', 36)->index();
            $table->char('scan_id', 36)->index();

            // Tipe evidence: screenshot, http_response, file_content, url_capture, manual
            $table->string('tipe', 50)->default('screenshot');

            // Label/caption untuk evidence
            $table->string('label', 255)->nullable();

            // URL remote screenshot (thum.io / screenshotone / urlscan)
            $table->text('remote_url')->nullable();

            // Path lokal jika di-download & disimpan
            $table->string('file_path', 500)->nullable();

            // Metadata tambahan (HTTP status, response headers, dll) dalam JSON
            $table->json('meta')->nullable();

            // Apakah evidence ini verified (diperiksa manual) atau auto-captured
            $table->boolean('is_verified')->default(false);

            $table->timestamps();

            $table->foreign('temuan_id')->references('id')->on('temuan')->onDelete('cascade');
            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temuan_evidence');
    }
};

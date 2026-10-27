<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah status & tracking resolusi di tabel temuan
        Schema::table('temuan', function (Blueprint $table) {
            $table->string('status', 30)->default('baru')->after('tingkat_kepercayaan')->index(); // baru, dalam_penanganan, selesai
            $table->timestamp('resolved_at')->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable()->after('resolved_by');
        });

        // 2. Buat tabel insiden (Incident Management)
        Schema::create('insiden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('temuan_id', 36)->nullable();
            $table->char('scan_id', 36)->nullable();
            $table->string('nomor_insiden', 50)->unique();
            $table->string('judul', 255);
            $table->text('deskripsi')->nullable();
            $table->string('tingkat_keparahan', 20)->default('sedang')->index(); // kritis, tinggi, sedang, rendah
            $table->string('status', 30)->default('open')->index(); // open, investigating, patching, resolved, closed
            $table->string('target', 255)->nullable();
            $table->string('assigned_to', 100)->nullable();
            $table->text('catatan_investigasi')->nullable();
            $table->text('solusi_remediasi')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('temuan_id')->references('id')->on('temuan')->nullOnDelete();
            $table->foreign('scan_id')->references('id')->on('scans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insiden');

        Schema::table('temuan', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['status', 'resolved_at', 'resolved_by', 'resolution_notes']);
        });
    }
};

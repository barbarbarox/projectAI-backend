<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi: Tambah kolom prediksi ML ke tabel temuan
 *
 * Pilar 1 — Integrasi Model ML (Random Forest) ke RedSim
 * Model ditraining dari 297K+ sampel NVD CVE 2022-2026.
 *
 * Kolom baru:
 *   ml_predicted_severity  — Prediksi severity dari ML model (CRITICAL/HIGH/MEDIUM/LOW/NONE)
 *   ml_confidence          — Confidence score model (0.0-1.0)
 *   konsensus_severity     — Gabungan LLM + ML (ambil yang lebih severe)
 *   ml_class_probabilities — Distribusi probabilitas per kelas (JSON)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan', function (Blueprint $table) {
            // Prediksi ML: CRITICAL / HIGH / MEDIUM / LOW / NONE
            // null jika ML service tidak tersedia (graceful degradation)
            $table->string('ml_predicted_severity', 20)
                ->nullable()
                ->after('tingkat_keparahan')
                ->comment('Prediksi severity dari Random Forest model');

            // Confidence score 0.0-1.0
            $table->float('ml_confidence', 5, 4)
                ->nullable()
                ->after('ml_predicted_severity')
                ->comment('Confidence score ML model (0.0-1.0)');

            // Konsensus: gabungan LLM + ML (ambil yang lebih severe)
            $table->string('konsensus_severity', 20)
                ->nullable()
                ->after('ml_confidence')
                ->comment('Severity konsensus LLM+ML (ambil lebih severe)');

            // Distribusi probabilitas per kelas
            $table->json('ml_class_probabilities')
                ->nullable()
                ->after('konsensus_severity')
                ->comment('Distribusi prob per kelas: {CRITICAL:0.85, HIGH:0.10, ...}');
        });
    }

    public function down(): void
    {
        Schema::table('temuan', function (Blueprint $table) {
            $table->dropColumn([
                'ml_predicted_severity',
                'ml_confidence',
                'konsensus_severity',
                'ml_class_probabilities',
            ]);
        });
    }
};

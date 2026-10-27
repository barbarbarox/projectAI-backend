<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Temuan extends Model
{
    protected $table = 'temuan';

    // UUID: non-incrementing string primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'scan_id',
        'tipe',
        'tingkat_keparahan',
        // ML Prediction — Pilar 1
        'ml_predicted_severity',
        'ml_confidence',
        'konsensus_severity',
        'ml_class_probabilities',
        // ─────────────────────
        'judul',
        'deskripsi',
        'lokasi',
        'nomor_baris',
        'kode_rentan',
        'kode_aman',
        'cve_id',
        'cwe_id',
        'capec_id',
        'teknik_attck',
        'tautan_referensi',
        'remediasi',
        'prioritas_perbaikan',
        'estimasi_usaha',
        'tingkat_kepercayaan',
        'is_disensor',
    ];

    protected function casts(): array
    {
        return [
            'is_disensor'            => 'boolean',
            'ml_confidence'          => 'float',
            'ml_class_probabilities' => 'array',
        ];
    }

    /**
     * Warna badge Bootstrap untuk severity LLM (tingkat_keparahan Indonesia).
     */
    public function getSeverityColorAttribute(): string
    {
        return match (strtolower($this->tingkat_keparahan ?? '')) {
            'kritis'  => 'danger',
            'tinggi'  => 'warning',
            'sedang'  => 'info',
            'rendah'  => 'secondary',
            default   => 'dark',
        };
    }

    /**
     * Warna badge Bootstrap untuk prediksi ML (English: CRITICAL/HIGH/MEDIUM/LOW).
     */
    public function getMlSeverityColorAttribute(): string
    {
        return match (strtoupper($this->ml_predicted_severity ?? '')) {
            'CRITICAL' => 'danger',
            'HIGH'     => 'warning',
            'MEDIUM'   => 'info',
            'LOW'      => 'secondary',
            'NONE'     => 'light',
            default    => 'dark',
        };
    }

    /**
     * Warna badge Bootstrap untuk severity konsensus.
     */
    public function getKonsensusColorAttribute(): string
    {
        return match (strtoupper($this->konsensus_severity ?? '')) {
            'CRITICAL' => 'danger',
            'HIGH'     => 'warning',
            'MEDIUM'   => 'info',
            'LOW'      => 'secondary',
            'NONE'     => 'light',
            default    => 'dark',
        };
    }

    public function setTipeAttribute($value): void
    {
        $this->attributes['tipe'] = !empty($value) ? substr((string)$value, 0, 100) : 'kerentanan';
    }

    public function setTingkatKeparahanAttribute($value): void
    {
        $val = strtolower(trim((string)$value));
        $map = [
            'kritis' => 'kritis', 'critical' => 'kritis',
            'tinggi' => 'tinggi', 'high' => 'tinggi',
            'sedang' => 'sedang', 'medium' => 'sedang',
            'rendah' => 'rendah', 'low' => 'rendah',
            'info' => 'info', 'informasi' => 'info', 'information' => 'info',
        ];
        $this->attributes['tingkat_keparahan'] = $map[$val] ?? 'info';
    }

    public function setEstimasiUsahaAttribute($value): void
    {
        $val = strtolower(trim((string)$value));
        $map = [
            'mudah' => 'mudah', 'easy' => 'mudah',
            'sedang' => 'sedang', 'medium' => 'sedang',
            'sulit' => 'sulit', 'hard' => 'sulit', 'difficult' => 'sulit',
        ];
        $this->attributes['estimasi_usaha'] = $map[$val] ?? 'sedang';
    }

    public function setTingkatKepercayaanAttribute($value): void
    {
        $val = strtolower(trim((string)$value));
        $map = [
            'tinggi' => 'tinggi', 'high' => 'tinggi',
            'sedang' => 'sedang', 'medium' => 'sedang',
            'rendah' => 'rendah', 'low' => 'rendah',
        ];
        $this->attributes['tingkat_kepercayaan'] = $map[$val] ?? 'sedang';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}

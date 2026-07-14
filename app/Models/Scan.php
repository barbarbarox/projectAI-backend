<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Scan extends Model
{
    // UUID: non-incrementing string primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'tipe_scan',
        'mode_scan',
        'mode_ai',
        'model_ai',
        'mode_rag',
        'is_verified_domain',
        'target',
        'nama_file',
        'status',
        'progress_step',
        'progress_persen',
        'skor_keamanan',
        'verdict',
        'verdict_deploy',
        'ringkasan_eksekutif',
        'ringkasan_teknis',
        'teknologi_terdeteksi',
        'confidence_score',
        'error_message',
        'data_mentah',
        'rag_references',
        'selesai_at',
        'dihapus_at',
    ];

    protected function casts(): array
    {
        return [
            'teknologi_terdeteksi' => 'array',
            'data_mentah' => 'array',
            'rag_references' => 'array',
            'selesai_at' => 'datetime',
            'dihapus_at' => 'datetime',
            'confidence_score' => 'float',
            'is_verified_domain' => 'boolean',
        ];
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function temuan(): HasMany
    {
        return $this->hasMany(Temuan::class);
    }

    public function simulasiSerangan(): HasMany
    {
        return $this->hasMany(SimulasiSerangan::class);
    }

    public function getVerdictLabelAttribute(): string
    {
        return match ($this->verdict) {
            'aman' => 'Aman',
            'perhatian' => 'Perlu Perhatian',
            'berbahaya' => 'Berbahaya',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'memproses' => 'Sedang Memproses',
            'selesai' => 'Selesai',
            'gagal' => 'Gagal',
            default => $this->status,
        };
    }
}

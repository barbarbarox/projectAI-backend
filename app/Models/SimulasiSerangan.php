<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SimulasiSerangan extends Model
{
    protected $table = 'simulasi_serangan';

    // UUID: non-incrementing string primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'scan_id',
        'nama_skenario',
        'profil_penyerang',
        'narasi_teknis',
        'narasi_eksekutif',
        'skor_kemungkinan',
        'skor_dampak',
        'rantai_serangan',
        'fase_attck',
    ];

    protected function casts(): array
    {
        return [
            'rantai_serangan' => 'array',
            'fase_attck' => 'array',
            'skor_kemungkinan' => 'float',
            'skor_dampak' => 'float',
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

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}

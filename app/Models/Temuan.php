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
            'is_disensor' => 'boolean',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insiden extends Model
{
    protected $table = 'insiden';

    protected $fillable = [
        'user_id',
        'temuan_id',
        'scan_id',
        'nomor_insiden',
        'judul',
        'deskripsi',
        'tingkat_keparahan',
        'status',
        'target',
        'assigned_to',
        'catatan_investigasi',
        'solusi_remediasi',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_insiden)) {
                $countToday = static::whereDate('created_at', today())->count() + 1;
                $model->nomor_insiden = 'INC-' . date('Ymd') . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function temuan(): BelongsTo
    {
        return $this->belongsTo(Temuan::class, 'temuan_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class, 'scan_id');
    }
}

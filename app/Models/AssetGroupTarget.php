<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetGroupTarget extends Model
{
    protected $table = 'asset_group_targets';

    protected $fillable = [
        'asset_group_id',
        'target_url',
        'label',
        'last_scan_id',
        'last_scanned_at',
        'last_skor_keamanan',
        'last_verdict',
    ];

    protected function casts(): array
    {
        return [
            'last_scanned_at'     => 'datetime',
            'last_skor_keamanan'  => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class, 'asset_group_id');
    }

    public function lastScan(): BelongsTo
    {
        return $this->belongsTo(Scan::class, 'last_scan_id');
    }

    public function getCleanUrlAttribute(): string
    {
        return preg_replace('#^https?://#', '', rtrim($this->target_url, '/'));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteBaseline extends Model
{
    protected $table = 'website_baselines';

    public $timestamps = false; // Hanya pakai created_at manual

    protected $fillable = [
        'monitor_id',
        'snapshot_at',
        'content_hash',
        'title',
        'h1_texts',
        'canonical_url',
        'meta_description',
        'link_hash',
        'external_scripts',
        'external_iframes',
        'inline_scripts_hash',
        'screenshot_path',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_at'         => 'datetime',
            'h1_texts'            => 'array',
            'external_scripts'    => 'array',
            'external_iframes'    => 'array',
            'inline_scripts_hash' => 'array',
            'is_current'          => 'boolean',
        ];
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(WebsiteMonitor::class, 'monitor_id');
    }

    /**
     * URL screenshot yang bisa diakses publik (jika ada).
     */
    public function getScreenshotUrlAttribute(): ?string
    {
        if (empty($this->screenshot_path)) {
            return null;
        }
        return asset('storage/' . $this->screenshot_path);
    }
}

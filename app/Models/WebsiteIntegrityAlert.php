<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteIntegrityAlert extends Model
{
    protected $table = 'website_integrity_alerts';

    public $timestamps = false; // Hanya created_at

    protected $fillable = [
        'monitor_id',
        'user_id',
        'check_type',
        'target_url',
        'detected_at',
        'alert_type',
        'severity',
        'diff_score',
        'evidence',
        'ai_analysis',
        'ai_risk_score',
        'is_read',
        'is_acknowledged',
        'notif_wa_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_at'       => 'datetime',
            'evidence'          => 'array',
            'is_read'           => 'boolean',
            'is_acknowledged'   => 'boolean',
            'notif_wa_sent_at'  => 'datetime',
            'diff_score'        => 'integer',
            'ai_risk_score'     => 'integer',
        ];
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(WebsiteMonitor::class, 'monitor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Badge severity untuk UI.
     */
    public function getSeverityBadgeAttribute(): array
    {
        return match ($this->severity) {
            'critical' => ['label' => 'Kritis',    'class' => 'bg-red-500/15 text-red-400 border-red-500/30',        'icon' => '🚨'],
            'high'     => ['label' => 'Tinggi',    'class' => 'bg-orange-500/15 text-orange-400 border-orange-500/30','icon' => '⚠️'],
            'warning'  => ['label' => 'Peringatan','class' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',  'icon' => '⚡'],
            default    => ['label' => 'Info',      'class' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',     'icon' => 'ℹ️'],
        };
    }

    /**
     * Label tipe alert yang ramah pengguna.
     */
    public function getAlertTypeLabelAttribute(): string
    {
        return match ($this->alert_type) {
            'content_change'  => 'Perubahan Konten',
            'script_inject'   => 'Injeksi Script Asing',
            'iframe_inject'   => 'Injeksi Iframe Asing',
            'defacement'      => 'Defacement Terdeteksi',
            'redirect_change' => 'Perubahan Redirect',
            'visual_change'   => 'Perubahan Visual',
            'seo_spam'        => 'Injeksi SEO Spam',
            'clean'           => 'Bersih',
            default           => ucfirst(str_replace('_', ' ', $this->alert_type)),
        };
    }

    /**
     * URL bersih tanpa skema.
     */
    public function getCleanUrlAttribute(): string
    {
        return preg_replace('#^https?://#', '', rtrim($this->target_url, '/'));
    }
}

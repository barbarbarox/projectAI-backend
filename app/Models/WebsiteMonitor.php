<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WebsiteMonitor extends Model
{
    protected $table = 'website_monitors';

    protected $fillable = [
        'user_id',
        'target_url',
        'nama',
        'status',
        'interval_menit',
        'is_dynamic_content',
        'threshold_pct',
        'alert_wa',
        'last_checked_at',
        'next_check_at',
        'consecutive_errors',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'is_dynamic_content' => 'boolean',
            'alert_wa'           => 'boolean',
            'last_checked_at'    => 'datetime',
            'next_check_at'      => 'datetime',
            'threshold_pct'      => 'integer',
            'interval_menit'     => 'integer',
            'consecutive_errors' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function baselines(): HasMany
    {
        return $this->hasMany(WebsiteBaseline::class, 'monitor_id');
    }

    public function currentBaseline(): HasOne
    {
        return $this->hasOne(WebsiteBaseline::class, 'monitor_id')
            ->where('is_current', true)
            ->latestOfMany('snapshot_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(WebsiteIntegrityAlert::class, 'monitor_id');
    }

    public function latestAlert(): HasOne
    {
        return $this->hasOne(WebsiteIntegrityAlert::class, 'monitor_id')
            ->latestOfMany('created_at');
    }

    /**
     * Cek apakah monitor ini siap untuk diperiksa.
     */
    public function isDueForCheck(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->next_check_at === null) {
            return true;
        }
        return $this->next_check_at->isPast();
    }

    /**
     * Hitung waktu pemeriksaan berikutnya.
     */
    public function scheduleNextCheck(): void
    {
        $this->update([
            'next_check_at'  => now()->addMinutes($this->interval_menit),
            'last_checked_at'=> now(),
        ]);
    }

    /**
     * Badge status untuk UI.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active'  => ['label' => 'Aktif',    'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30'],
            'paused'  => ['label' => 'Dijeda',   'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/30'],
            'error'   => ['label' => 'Error',    'class' => 'bg-red-500/15 text-red-400 border-red-500/30'],
            default   => ['label' => 'Tidak Diketahui', 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/30'],
        };
    }

    /**
     * URL bersih tanpa skema untuk tampilan.
     */
    public function getCleanUrlAttribute(): string
    {
        return preg_replace('#^https?://#', '', rtrim($this->target_url, '/'));
    }

    /**
     * Jumlah alert belum dibaca untuk monitor ini.
     */
    public function getUnreadAlertsCountAttribute(): int
    {
        return $this->alerts()->where('is_read', false)->count();
    }
}

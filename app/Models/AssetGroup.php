<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetGroup extends Model
{
    protected $table = 'asset_groups';

    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'warna',
        'environment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(AssetGroupTarget::class, 'asset_group_id');
    }

    /**
     * Hitung rata-rata skor keamanan target dalam grup.
     */
    public function getRataRataSkorAttribute(): ?int
    {
        $avg = $this->targets()->whereNotNull('last_skor_keamanan')->avg('last_skor_keamanan');
        return $avg !== null ? (int) round($avg) : null;
    }

    /**
     * Helper tema warna badge & border kartu.
     */
    public function getColorThemeAttribute(): array
    {
        return match ($this->warna) {
            'cyan'    => ['border' => 'border-cyan-500/40',    'bg' => 'bg-cyan-500/10',    'text' => 'text-cyan-400',    'gradient' => 'from-cyan-500 to-blue-500'],
            'emerald' => ['border' => 'border-emerald-500/40', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'gradient' => 'from-emerald-500 to-teal-500'],
            'amber'   => ['border' => 'border-amber-500/40',   'bg' => 'bg-amber-500/10',   'text' => 'text-amber-400',   'gradient' => 'from-amber-500 to-orange-500'],
            'purple'  => ['border' => 'border-purple-500/40',  'bg' => 'bg-purple-500/10',  'text' => 'text-purple-400',  'gradient' => 'from-purple-500 to-indigo-500'],
            'rose'    => ['border' => 'border-rose-500/40',    'bg' => 'bg-rose-500/10',    'text' => 'text-rose-400',    'gradient' => 'from-rose-500 to-red-500'],
            default   => ['border' => 'border-indigo-500/40',  'bg' => 'bg-indigo-500/10',  'text' => 'text-indigo-400',  'gradient' => 'from-indigo-500 to-cyan-500'],
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemuanEvidence extends Model
{
    protected $table = 'temuan_evidence';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'temuan_id',
        'scan_id',
        'tipe',
        'label',
        'remote_url',
        'file_path',
        'meta',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'meta'        => 'array',
            'is_verified' => 'boolean',
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
        // Hapus file lokal saat record dihapus
        static::deleted(function ($model) {
            if ($model->file_path) {
                Storage::disk('public')->delete($model->file_path);
            }
        });
    }

    // ── Relations ──────────────────────────────────────────

    public function temuan(): BelongsTo
    {
        return $this->belongsTo(Temuan::class);
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    // ── Accessors ──────────────────────────────────────────

    /**
     * URL yang bisa langsung dipakai di <img src="">
     * Prioritas: file lokal (public storage) → remote URL
     */
    public function getDisplayUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }
        return $this->remote_url;
    }

    /**
     * Label tipe yang mudah dibaca
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'screenshot'    => 'Screenshot',
            'http_response' => 'HTTP Response',
            'file_content'  => 'File Content',
            'url_capture'   => 'URL Capture',
            'manual'        => 'Manual Upload',
            default         => ucfirst($this->tipe),
        };
    }
}

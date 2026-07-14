<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DomainVerification extends Model
{
    protected $table = 'domain_verifications';

    // UUID: non-incrementing string primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'domain',
        'token',
        'nama_file',
        'status',
        'verified_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
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

    /**
     * Cek apakah domain sudah terverifikasi untuk user tertentu.
     */
    public function scopeVerifiedForUser($query, string $domain, int $userId)
    {
        return $query->where('domain', $domain)
            ->where('user_id', $userId)
            ->where('status', 'verified');
    }

    /**
     * Cek apakah verifikasi sudah expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isPast();
    }
}

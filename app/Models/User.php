<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nama_lengkap',
        'email',
        'phone',
        'password',
        'google_id',
        'avatar',
        'scan_count_today',
        'last_scan_reset',
        'tier',
        'is_verified',
        'last_login',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_scan_reset' => 'date',
            'last_login' => 'datetime',
            'last_seen_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function poinUser(): HasMany
    {
        return $this->hasMany(PoinUser::class);
    }

    public function getTotalPoinAttribute(): int
    {
        return $this->poinUser()->where('is_benar', true)->sum('poin_diperoleh');
    }
}

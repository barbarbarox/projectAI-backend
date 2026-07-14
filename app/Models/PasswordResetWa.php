<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetWa extends Model
{
    protected $table = 'password_resets_wa';

    protected $fillable = [
        'phone',
        'token',
        'expires_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }

    /**
     * Cek apakah token masih valid (belum expired dan belum digunakan).
     */
    public function isValid(): bool
    {
        return !$this->used && now()->lt($this->expires_at);
    }
}

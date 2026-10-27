<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAiConfig extends Model
{
    protected $fillable = [
        'user_id', 'method', 'provider', 'label',
        'api_key', 'oauth_access_token', 'oauth_refresh_token',
        'oauth_expires_at', 'available_models', 'selected_model',
        'is_active', 'last_verified_at', 'last_used_at', 'total_requests',
    ];

    protected function casts(): array
    {
        return [
            'available_models' => 'array',
            'is_active' => 'boolean',
            'api_key' => 'encrypted',
            'oauth_access_token' => 'encrypted',
            'oauth_refresh_token' => 'encrypted',
            'oauth_expires_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ambil config AI aktif milik user tertentu.
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Cek apakah OAuth token sudah expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->oauth_expires_at) return true;
        return $this->oauth_expires_at->isPast();
    }

    /**
     * Ambil key/token yang aktif untuk digunakan API call.
     */
    public function getActiveKey(): ?string
    {
        if (in_array($this->method, ['oauth_google', 'oauth_openai'])) {
            return $this->oauth_access_token;
        }
        return $this->api_key;
    }

    /**
     * Label provider yang human-readable.
     */
    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            'google' => 'Google AI (Gemini)',
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic (Claude)',
            'nvidia' => 'NVIDIA NIM',
            'groq' => 'Groq',
            'openrouter' => 'OpenRouter',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Label metode yang human-readable.
     */
    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'api_key' => 'API Key',
            'oauth_google' => 'Google OAuth',
            'oauth_openai' => 'OpenAI OAuth',
            default => $this->method,
        };
    }
}

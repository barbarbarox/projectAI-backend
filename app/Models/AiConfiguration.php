<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConfiguration extends Model
{
    protected $fillable = [
        'provider', 'label', 'api_key', 'detected_provider',
        'available_models', 'selected_model',
        'is_active', 'is_default', 'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'available_models' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_verified_at' => 'datetime',
            'api_key' => 'encrypted',
        ];
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    public static function getActiveWithModel(): ?self
    {
        return static::where('is_active', true)
            ->whereNotNull('selected_model')
            ->orderByDesc('is_default')
            ->first();
    }
}

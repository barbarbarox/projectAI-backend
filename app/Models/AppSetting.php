<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Set/update nilai setting.
     */
    public static function setValue(string $key, string $value, ?string $description = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'description' => $description,
            ])
        );
    }
}

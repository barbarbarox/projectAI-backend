<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoinUser extends Model
{
    protected $table = 'poin_user';

    protected $fillable = [
        'user_id',
        'tantangan_id',
        'poin_diperoleh',
        'jawaban_user',
        'is_benar',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'is_benar' => 'boolean',
            'selesai_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tantangan(): BelongsTo
    {
        return $this->belongsTo(Tantangan::class);
    }
}

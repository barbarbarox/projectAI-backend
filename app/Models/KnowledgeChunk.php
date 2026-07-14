<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable = [
        'content',
        'embedding',
        'source',
        'source_id',
        'title',
        'metadata',
        'chunk_index',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}

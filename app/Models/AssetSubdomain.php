<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetSubdomain extends Model
{
    protected $table = 'asset_subdomains';

    protected $fillable = [
        'scan_id',
        'user_id',
        'subdomain',
        'root_domain',
        'ip_address',
        'cname',
        'status_http',
        'is_alive',
        'takeover_risk',
        'takeover_service',
        'takeover_evidence',
        'is_interesting',
        'server_header',
        'page_title',
    ];

    protected function casts(): array
    {
        return [
            'is_alive'        => 'boolean',
            'takeover_risk'   => 'boolean',
            'is_interesting'  => 'boolean',
            'status_http'     => 'integer',
        ];
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class, 'scan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

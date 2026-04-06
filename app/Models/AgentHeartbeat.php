<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AgentHeartbeat extends Model
{
    protected $fillable = [
        'agent_id',
        'status',
        'ip_address',
        'os_type',
        'agent_version',
        'host_fingerprint',
        'metadata',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'seen_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

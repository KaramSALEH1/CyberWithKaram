<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'user_id',
        'agent_key',
        'api_token_hash',
        'device_name',
        'ip_address',
        'os_type',
        'agent_version',
        'host_fingerprint',
        'status',
        'last_seen',
        'token_last_rotated_at',
        'last_nonce',
        'metadata',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
            'token_last_rotated_at' => 'datetime',
            'registered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(AgentCommand::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(AgentHeartbeat::class);
    }
}

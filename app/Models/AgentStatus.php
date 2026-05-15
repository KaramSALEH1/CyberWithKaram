<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentStatus extends Model
{
    protected $fillable = [
        'service_id',
        'user_id',
        'last_heartbeat',
        'status',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}

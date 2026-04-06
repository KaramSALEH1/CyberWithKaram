<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class AgentCommand extends Model
{
    protected $fillable = [
        'command_uuid',
        'agent_id',
        'requested_by',
        'approved_by',
        'command_key',
        'payload',
        'signature_hash',
        'nonce',
        'expires_at',
        'status',
        'queued_at',
        'sent_at',
        'started_at',
        'finished_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'expires_at' => 'datetime',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function result(): HasOne
    {
        return $this->hasOne(AgentCommandResult::class);
    }
}

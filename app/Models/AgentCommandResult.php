<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AgentCommandResult extends Model
{
    protected $fillable = [
        'agent_command_id',
        'result_status',
        'exit_code',
        'duration_ms',
        'stdout',
        'stderr',
        'result_hash',
        'artifacts',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'artifacts' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function command(): BelongsTo
    {
        return $this->belongsTo(AgentCommand::class, 'agent_command_id');
    }
}

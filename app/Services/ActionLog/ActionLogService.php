<?php

namespace App\Services\ActionLog;

use App\Models\ActionLog;
use App\Models\User;

class ActionLogService
{
    public function log(?User $user, string $description): ActionLog
    {
        return ActionLog::create([
            'user_id' => $user?->id,
            'action_description' => $description,
            'logged_at' => now(),
        ]);
    }
}

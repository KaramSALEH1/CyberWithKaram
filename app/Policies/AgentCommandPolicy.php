<?php

namespace App\Policies;

use App\Models\AgentCommand;
use App\Models\User;
class AgentCommandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, AgentCommand $agentCommand): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, AgentCommand $agentCommand): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, AgentCommand $agentCommand): bool
    {
        return $user->is_admin;
    }
}

<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use App\Services\Academy\EntitlementService;

class ModulePolicy
{
    public function view(User $user, Module $module): bool
    {
        return app(EntitlementService::class)->userHasModuleAccess($user, $module);
    }
}

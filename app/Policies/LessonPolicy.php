<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use App\Services\Academy\EntitlementService;

class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        return app(EntitlementService::class)->userHasLessonAccess($user, $lesson);
    }
}

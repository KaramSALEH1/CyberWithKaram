<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Services\Academy\EntitlementService;

class CoursePolicy
{
    public function view(User $user, Course $course): bool
    {
        return app(EntitlementService::class)->userHasCourseAccess($user, $course);
    }
}

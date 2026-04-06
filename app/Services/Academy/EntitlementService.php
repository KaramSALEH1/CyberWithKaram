<?php

namespace App\Services\Academy;

use App\Models\Course;
use App\Models\Entitlement;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;

class EntitlementService
{
    public function userHasLessonAccess(User $user, Lesson $lesson): bool
    {
        if (!$lesson->requires_purchase && !$lesson->module->requires_purchase && !$lesson->module->course->requires_purchase) {
            return true;
        }

        return $this->hasEntitlement($user, 'lesson', $lesson->id)
            || $this->hasEntitlement($user, 'module', $lesson->module_id)
            || $this->hasEntitlement($user, 'course', $lesson->module->course_id);
    }

    public function userHasModuleAccess(User $user, Module $module): bool
    {
        if (!$module->requires_purchase && !$module->course->requires_purchase) {
            return true;
        }

        return $this->hasEntitlement($user, 'module', $module->id)
            || $this->hasEntitlement($user, 'course', $module->course_id);
    }

    public function userHasCourseAccess(User $user, Course $course): bool
    {
        if (!$course->requires_purchase) {
            return true;
        }

        return $this->hasEntitlement($user, 'course', $course->id);
    }

    private function hasEntitlement(User $user, string $type, int $id): bool
    {
        return Entitlement::query()
            ->where('user_id', $user->id)
            ->where('entitlement_type', $type)
            ->where('entitlement_id', $id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }
}

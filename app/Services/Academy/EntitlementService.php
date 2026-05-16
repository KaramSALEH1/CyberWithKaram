<?php

namespace App\Services\Academy;

use App\Models\Course;
use App\Models\Entitlement;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;

class EntitlementService
{
    public function userHasCourseAccess(User $user, Course $course): bool
    {
        if ($user->is_admin) {
            return true;
        }

        // Check entitlements for the course
        return $this->hasEntitlement($user, 'course', $course->id);
    }

    public function userHasLessonAccess(User $user, Lesson $lesson): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($lesson->is_free) {
            return true;
        }

        // Hierarchical Inheritance:
        // 1. Check Course Access
        if ($this->userHasCourseAccess($user, $lesson->module->course)) {
            return true;
        }

        // 2. Check Module Access
        if ($this->userHasModuleAccess($user, $lesson->module)) {
            return true;
        }

        // 3. Check Specific Lesson Access
        if ($this->hasEntitlement($user, 'lesson', $lesson->id)) {
            return true;
        }

        // If the lesson itself doesn't require purchase, and neither does the module or course
        if (!$lesson->requires_purchase && !$lesson->module->requires_purchase && !$lesson->module->course->requires_purchase) {
            return true;
        }

        return false;
    }

    public function userHasModuleAccess(User $user, Module $module): bool
    {
        if ($user->is_admin) {
            return true;
        }

        // 1. Check Course Access (Inherited)
        if ($this->userHasCourseAccess($user, $module->course)) {
            return true;
        }

        // 2. Check Specific Module Access
        return $this->hasEntitlement($user, 'module', $module->id);
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

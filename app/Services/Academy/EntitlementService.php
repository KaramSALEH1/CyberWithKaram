<?php

namespace App\Services\Academy;

use App\Models\Course;
use App\Models\Entitlement;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Payment;
use App\Models\User;

class EntitlementService
{
    public function userHasCourseAccess(User $user, Course $course): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($this->hasApprovedPayment($user, 'course', $course->id)) {
            return true;
        }

        if ($this->hasEntitlement($user, 'course', $course->id)) {
            return true;
        }

        if (! $course->requires_purchase && (float) $course->price <= 0) {
            return true;
        }

        return false;
    }

    public function userHasLessonAccess(User $user, Lesson $lesson): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($lesson->is_free) {
            return true;
        }

        $lesson->loadMissing('module.course');

        if ($this->userHasCourseAccess($user, $lesson->module->course)) {
            return true;
        }

        if ($this->userHasModuleAccess($user, $lesson->module)) {
            return true;
        }

        if ($this->hasApprovedPayment($user, 'lesson', $lesson->id)) {
            return true;
        }

        if ($this->hasEntitlement($user, 'lesson', $lesson->id)) {
            return true;
        }

        if (
            ! $lesson->requires_purchase
            && ! $lesson->module->requires_purchase
            && ! $lesson->module->course->requires_purchase
            && (float) $lesson->price <= 0
            && (float) $lesson->module->price <= 0
            && (float) $lesson->module->course->price <= 0
        ) {
            return true;
        }

        return false;
    }

    public function userHasModuleAccess(User $user, Module $module): bool
    {
        if ($user->is_admin) {
            return true;
        }

        $module->loadMissing('course');

        if ($this->userHasCourseAccess($user, $module->course)) {
            return true;
        }

        if ($this->hasApprovedPayment($user, 'module', $module->id)) {
            return true;
        }

        if ($this->hasEntitlement($user, 'module', $module->id)) {
            return true;
        }

        if (! $module->requires_purchase && ! $module->course->requires_purchase && (float) $module->price <= 0 && (float) $module->course->price <= 0) {
            return true;
        }

        return false;
    }

    private function hasApprovedPayment(User $user, string $productType, int $productId): bool
    {
        return Payment::query()
            ->where('user_id', $user->id)
            ->where('product_type', $productType)
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->exists();
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

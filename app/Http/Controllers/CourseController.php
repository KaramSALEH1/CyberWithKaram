<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\Academy\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    public function index()
    {
        $courses = Course::with('modules.lessons')->where('is_active', true)->get();
        return view('courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $course->load(['modules.lessons' => function ($query) {
            $query->orderBy('order_no');
        }]);

        $hasCourseAccess = Auth::check() && $this->entitlementService->userHasCourseAccess(Auth::user(), $course);

        return view('courses.show', compact('course', 'hasCourseAccess'));
    }

    public function showLesson($course_slug, $lesson_slug)
    {
        // 1. Fetch the course to ensure it exists
        $course = Course::where('slug', $course_slug)->firstOrFail();

        // 2. Fetch the lesson by its slug and make sure it belongs to a module within this course
        $lesson = Lesson::where('slug', $lesson_slug)
            ->whereHas('module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->firstOrFail();

        $user = Auth::user();

        // 3. Keep our cascading access check secure
        $hasAccess = $user && $this->entitlementService->userHasCourseAccess($user, $course);

        // Security Check
        if (!$lesson->is_free && (!$user || !$this->entitlementService->userHasLessonAccess($user, $lesson))) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', '🔒 You must purchase this Academy Track to unlock this lesson.');
        }

        // 4. Load the course curriculum for the sidebar navigation
        $course->load(['modules.lessons' => function ($query) {
            $query->orderBy('order_no');
        }]);

        // Find next and previous lessons
        $allLessons = $course->modules->pluck('lessons')->flatten();
        $currentIndex = $allLessons->search(fn($l) => $l->id === $lesson->id);

        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        return view('lessons.show', compact('course', 'lesson', 'prevLesson', 'nextLesson', 'hasAccess'));
    }

    public function pay(Request $request, Course $course)
    {
        return redirect()->route('courses.checkout', $course->slug);
    }
}

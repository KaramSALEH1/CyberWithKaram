<?php

namespace App\Http\Controllers;

use App\Models\{Course, Module, Lesson};
use App\Support\SecureVideoUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcademyController extends Controller
{
    public function index()
    {
        $courses = Course::with('modules.lessons')->get();
        return view('admin.acade.index', compact('courses'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate(['title' => 'required', 'level' => 'required', 'price' => 'nullable|numeric|min:0']);
        Course::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'level' => $request->level,
            'price' => $request->price ?? 0,
            'is_active' => true,
            'requires_purchase' => $request->boolean('requires_purchase'),
        ]);
        return back()->with('success', 'Course Created!');
    }

    public function storeModule(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required',
            'price' => 'nullable|numeric|min:0',
        ]);
        Module::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'price' => $request->price,
            'order_no' => Module::where('course_id', $request->course_id)->count() + 1,
            'requires_purchase' => $request->boolean('requires_purchase'),
        ]);
        return back()->with('success', 'Module Added!');
    }

    public function storeLesson(Request $request)
    {
        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'video_type' => 'required|in:youtube,local',
            'video_url' => 'nullable|required_if:video_type,youtube|string|max:255',
            'video_file' => 'nullable|required_if:video_type,local|file|mimes:mp4,mov,avi,wmv|max:102400',
            'content' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'requires_purchase' => 'boolean',
        ]);

        $videoPath = null;
        $videoUrl = null;

        if ($data['video_type'] === 'local') {
            if (! $request->hasFile('video_file')) {
                throw ValidationException::withMessages([
                    'video_file' => 'A video file is required for local uploads.',
                ]);
            }
            $videoPath = SecureVideoUpload::store($request->file('video_file'));
        } else {
            $videoUrl = $data['video_url'];
        }

        Lesson::create([
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'slug' => $this->uniqueLessonSlug($data['title'], (int) $data['module_id']),
            'video_type' => $data['video_type'],
            'video_url' => $videoUrl,
            'video_path' => $videoPath,
            'content' => $data['content'] ?? null,
            'price' => $data['price'] ?? null,
            'order_no' => Lesson::where('module_id', $data['module_id'])->count() + 1,
            'is_free' => $request->boolean('is_free'),
            'requires_purchase' => $request->boolean('requires_purchase'),
        ]);

        return back()->with('success', 'Lesson Published!');
    }

    public function destroyCourse(Course $course)
    {
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }

    public function editCourse(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $request->validate(['title' => 'required', 'level' => 'required', 'price' => 'nullable|numeric|min:0']);
        $course->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'level' => $request->level,
            'price' => $request->price ?? 0,
            'requires_purchase' => $request->boolean('requires_purchase'),
        ]);
        return redirect()->route('admin.academy.index')->with('success', 'Course updated!');
    }

    public function editModule(Module $module)
    {
        return view('admin.modules.edit', compact('module'));
    }

    public function updateModule(Request $request, Module $module)
    {
        $request->validate(['title' => 'required', 'price' => 'nullable|numeric|min:0']);
        $module->update([
            'title' => $request->title,
            'price' => $request->price,
            'requires_purchase' => $request->boolean('requires_purchase'),
        ]);
        return redirect()->route('admin.course.show', $module->course_id)->with('success', 'Module updated!');
    }

    public function destroyModule(Module $module)
    {
        $courseId = $module->course_id;
        $module->delete();
        return redirect()->route('admin.course.show', $courseId)->with('success', 'Module deleted.');
    }

    public function editLesson(Lesson $lesson)
    {
        return view('admin.lessons.edit', compact('lesson'));
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'video_type' => 'required|in:youtube,local',
            'video_url' => 'nullable|required_if:video_type,youtube|string|max:255',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400',
            'content' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'requires_purchase' => 'boolean',
        ]);

        if ($data['video_type'] === 'local' && ! $request->hasFile('video_file') && ! $lesson->video_path) {
            throw ValidationException::withMessages([
                'video_file' => 'A video file is required when using local delivery.',
            ]);
        }

        $updateData = [
            'title' => $data['title'],
            'slug' => $this->uniqueLessonSlug($data['title'], $lesson->module_id, $lesson->id),
            'video_type' => $data['video_type'],
            'content' => $data['content'] ?? null,
            'price' => $data['price'],
            'is_free' => $request->boolean('is_free'),
            'requires_purchase' => $request->boolean('requires_purchase'),
        ];

        if ($data['video_type'] === 'youtube') {
            $updateData['video_url'] = $data['video_url'];
            $updateData['video_path'] = null;
        } else {
            $updateData['video_url'] = null;
            if ($request->hasFile('video_file')) {
                $updateData['video_path'] = SecureVideoUpload::store($request->file('video_file'));
            }
        }

        $lesson->update($updateData);

        return redirect()->route('admin.course.show', $lesson->module->course_id)->with('success', 'Lesson updated!');
    }

    public function destroyLesson(Lesson $lesson)
    {
        $courseId = $lesson->module->course_id;
        $lesson->delete();
        return redirect()->route('admin.course.show', $courseId)->with('success', 'Lesson deleted.');
    }

    private function uniqueLessonSlug(string $title, int $moduleId, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (
            Lesson::where('module_id', $moduleId)
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}

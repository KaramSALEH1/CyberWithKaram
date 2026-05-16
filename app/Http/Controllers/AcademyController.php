<?php

namespace App\Http\Controllers;

use App\Models\{Course, Module, Lesson};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $request->validate(['title' => 'required', 'price' => 'nullable|numeric|min:0']);
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
            'video_url' => 'nullable|required_if:video_type,youtube|string',
            'video_file' => 'nullable|required_if:video_type,local|file|mimes:mp4,mov,avi,wmv|max:102400', // 100MB max
            'content' => 'nullable|string',
            'is_free' => 'boolean',
            'requires_purchase' => 'boolean',
        ]);

        $videoPath = null;
        if ($request->video_type === 'local' && $request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('videos', 'public');
        }

        Lesson::create([
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'video_type' => $data['video_type'],
            'video_url' => $data['video_url'],
            'video_path' => $videoPath,
            'content' => $data['content'],
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
            'video_url' => 'nullable|required_if:video_type,youtube|string',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400',
            'content' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'requires_purchase' => 'boolean',
        ]);

        $updateData = [
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'video_type' => $data['video_type'],
            'video_url' => $data['video_url'],
            'content' => $data['content'],
            'price' => $data['price'],
            'is_free' => $request->boolean('is_free'),
            'requires_purchase' => $request->boolean('requires_purchase'),
        ];

        if ($request->video_type === 'local' && $request->hasFile('video_file')) {
            $updateData['video_path'] = $request->file('video_file')->store('videos', 'public');
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
}

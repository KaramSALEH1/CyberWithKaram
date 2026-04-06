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
        return view('admin.academy.index', compact('courses'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate(['title' => 'required', 'level' => 'required']);
        Course::create(['title' => $request->title, 'slug' => Str::slug($request->title), 'description' => $request->description, 'level' => $request->level]);
        return back()->with('success', 'Course Created!');
    }

    public function storeModule(Request $request)
    {
        Module::create(['course_id' => $request->course_id, 'title' => $request->title, 'order_no' => Module::where('course_id', $request->course_id)->count() + 1]);
        return back()->with('success', 'Module Added!');
    }

    public function storeLesson(Request $request)
    {
        Lesson::create(['module_id' => $request->module_id, 'title' => $request->title, 'video_url' => $request->video_url, 'content' => $request->content, 'order_no' => Lesson::where('module_id', $request->module_id)->count() + 1]);
        return back()->with('success', 'Lesson Published!');
    }
}

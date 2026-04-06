<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // عرض لوحة التحكم الرئيسية
    public function index()
    {
        $services = Service::all();
        $lessons = Lesson::orderBy('order_no')->get();
        return view('dashboard', compact('services', 'lessons'));
    }

    // إضافة درس كورس جديد
    public function storeLesson(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'youtube_url' => 'required',
        ]);

        // استخراج الـ ID من رابط يوتيوب
        parse_str(parse_url($request->youtube_url, PHP_URL_QUERY), $vars);
        $videoId = $vars['v'] ?? $request->youtube_url;

        $moduleId = Module::query()->value('id');
        if (!$moduleId) {
            return back()->withErrors(['lesson' => 'Create a module first before adding lessons from dashboard.']);
        }

        Lesson::create([
            'module_id' => $moduleId,
            'title' => $request->title,
            'video_url' => $videoId,
            'content' => $request->description ?? '',
            'order_no' => Lesson::where('module_id', $moduleId)->count() + 1,
        ]);

        return back()->with('success', 'Lesson added to your course!');
    }
}

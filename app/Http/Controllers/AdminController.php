<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Lesson;
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

    // حفظ خدمة جديدة أو تحديث موجودة
    public function storeService(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'description' => 'required',
        ]);

        Service::updateOrCreate(
            ['slug' => \str()::slug($request->title)],
            [
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'icon' => $request->icon ?? '🛠️',
                'is_automated' => $request->has('is_automated'),
            ]
        );

        return back()->with('success', 'Service updated successfully!');
    }

    // إظهار أو إخفاء خدمة
    public function toggleService($id)
    {
        $service = Service::findOrFail($id);
        $service->is_visible = !$service->is_visible;
        $service->save();

        return back();
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

        Lesson::create([
            'title' => $request->title,
            'youtube_url' => $videoId,
            'description' => $request->description ?? '',
            'order_no' => Lesson::count() + 1,
        ]);

        return back()->with('success', 'Lesson added to your course!');
    }
}

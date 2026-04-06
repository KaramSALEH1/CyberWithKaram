<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AcademyController,
    AdminController,
    ProfileController,
    PageController
};
use App\Models\{Service, Lesson, Course};

/*
|--------------------------------------------------------------------------
| Public Routes (المسارات العامة للزوار)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $services = Service::where('is_visible', true)->get();
    return view('welcome', compact('services'));
})->name('home');

Route::get('/about', fn() => view('about'))->name('about');
Route::get('/contact', fn() => view('contact'))->name('contact');

// الخدمات
Route::get('/services', function () {
    $services = Service::where('is_visible', true)->get();
    return view('services', compact('services'));
})->name('services');

Route::get('/services/{slug}', function ($slug) {
    $service = Service::where('slug', $slug)->firstOrFail();
    return view('service-details', compact('service'));
})->name('service.show');

// الأكاديمية (العرض للجمهور)
Route::get('/courses', function () {
    $courses = Course::with('modules.lessons')->where('is_active', true)->get();
    return view('courses', compact('courses'));
})->name('courses');


/*
|--------------------------------------------------------------------------
| Admin Routes (لوحة التحكم - تحتاج تسجيل دخول)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

    // الداشبورد الرئيسي
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // --- قسم الأكاديمية المطور ---
    Route::prefix('academy')->group(function () {
        Route::get('/', [AcademyController::class, 'index'])->name('admin.academy.index');

        // الكورسات
        Route::post('/course/store', [AcademyController::class, 'storeCourse'])->name('admin.course.store');
        Route::delete('/course/{id}', [AcademyController::class, 'destroyCourse'])->name('admin.course.delete');

        // الوحدات (Modules)
        Route::post('/module/store', [AcademyController::class, 'storeModule'])->name('admin.module.store');

        // الدروس (Lessons)
        Route::post('/lesson/store', [AcademyController::class, 'storeLesson'])->name('admin.lesson.store');
    });

    // --- قسم الخدمات السيبرانية ---
    Route::prefix('services')->group(function () {
        Route::post('/store', [AdminController::class, 'storeService'])->name('admin.services.store');
        Route::post('/toggle/{id}', [AdminController::class, 'toggleService'])->name('admin.services.toggle');
    });

    // --- إدارة الملف الشخصي ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

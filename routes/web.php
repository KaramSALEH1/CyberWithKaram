<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\{
    AcademyController,
    AdminController,
    Admin\CommandCenterController,
    Admin\ServiceController,
    ProfileController,
    PageController
};
use App\Models\{Service, Lesson, Course, Module};
use App\Services\Academy\EntitlementService;

/*
|--------------------------------------------------------------------------
| Public Routes (المسارات العامة للزوار)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $services = Schema::hasTable('services')
        ? Service::where('is_visible', true)->get()
        : collect();
    return view('welcome', compact('services'));
})->name('home');

Route::get('/about', fn() => view('about'))->name('about');
Route::get('/contact', fn() => view('contact'))->name('contact');

// الخدمات
Route::get('/services', function () {
    $services = Schema::hasTable('services')
        ? Service::where('is_visible', true)->get()
        : collect();
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

Route::middleware(['auth', 'verified'])->prefix('academy')->group(function () {
    Route::get('/courses/{course}', function (Course $course, EntitlementService $entitlementService) {
        abort_unless($entitlementService->userHasCourseAccess(request()->user(), $course), 403);
        return response()->json(['course' => $course]);
    })->name('academy.course.show');

    Route::get('/modules/{module}', function (Module $module, EntitlementService $entitlementService) {
        abort_unless($entitlementService->userHasModuleAccess(request()->user(), $module), 403);
        return response()->json(['module' => $module]);
    })->name('academy.module.show');

    Route::get('/lessons/{lesson}', function (Lesson $lesson, EntitlementService $entitlementService) {
        abort_unless($entitlementService->userHasLessonAccess(request()->user(), $lesson->load('module.course')), 403);
        return response()->json(['lesson' => $lesson]);
    })->name('academy.lesson.show');
});


/*
|--------------------------------------------------------------------------
| Admin Routes (لوحة التحكم - تحتاج تسجيل دخول)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

    // الداشبورد الرئيسي
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/command-center', [CommandCenterController::class, 'index'])->name('admin.command-center.index');
    Route::post('/command-center/commands', [CommandCenterController::class, 'store'])->name('admin.command-center.commands.store');
    Route::post('/command-center/commands/{command}/cancel', [CommandCenterController::class, 'cancel'])->name('admin.command-center.commands.cancel');

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

    Route::resource('/services', ServiceController::class)->names([
        'index' => 'admin.services.index',
        'create' => 'admin.services.create',
        'store' => 'admin.services.store',
        'show' => 'admin.services.show',
        'edit' => 'admin.services.edit',
        'update' => 'admin.services.update',
        'destroy' => 'admin.services.destroy',
    ]);

    Route::post('/lessons/store', [AdminController::class, 'storeLesson'])->name('admin.dashboard.lesson.store');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

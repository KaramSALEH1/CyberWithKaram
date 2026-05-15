<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\{
    AcademyController,
    AdminController,
    Admin\CommandCenterController,
    Admin\CourseController,
    Admin\PaymentController as AdminPaymentController,
    Admin\ServiceController,
    PaymentController,
    ProfileController,
    UserToolController,
};
use App\Models\Payment;
use App\Services\Payment\PaymentVerificationService;
use App\Models\{Service, Lesson, Course, Module};
use App\Services\Academy\EntitlementService;

Route::get('/', function () {
    $services = Schema::hasTable('services')
        ? Service::where('is_visible', true)->get()
        : collect();
    return view('welcome', compact('services'));
})->name('home');

Route::get('/about', fn() => view('about'))->name('about');
Route::get('/contact', fn() => view('contact'))->name('contact');

Route::get('/services', function () {
    $services = Schema::hasTable('services')
        ? Service::where('is_visible', true)->get()
        : collect();
    return view('services', compact('services'));
})->name('services');

Route::get('/services/{service:slug}', function (Service $service, PaymentVerificationService $verificationService) {
    $hasApprovedAccess = false;
    $userLicenseKey = null; // To store the license key if purchased and approved

    if (Auth::check()) {
        // Check if the user has an approved payment for this service
        $approvedPayment = Payment::query()
            ->where('user_id', Auth::id())
            ->where('service_id', $service->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if ($approvedPayment) {
            $hasApprovedAccess = true;
            $userLicenseKey = $approvedPayment->license_key;
        }
    }

    return view('service-details', compact('service', 'hasApprovedAccess', 'userLicenseKey'));
})->name('service.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/services/{service:slug}/pay', [PaymentController::class, 'create'])->name('services.pay');
    Route::post('/services/{service:slug}/pay', [PaymentController::class, 'store'])->name('services.pay.store');
    Route::get('/services/{service:slug}/pay/global-success', [PaymentController::class, 'mockGlobalPaymentSuccess'])->name('payment.mock-global-success');
});

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

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/command-center', [CommandCenterController::class, 'index'])->name('admin.command-center.index');
    Route::post('/command-center/commands', [CommandCenterController::class, 'store'])->name('admin.command-center.commands.store');
    Route::post('/command-center/commands/{command}/cancel', [CommandCenterController::class, 'cancel'])->name('admin.command-center.commands.cancel');

    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');
    Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('admin.payments.approve');
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('admin.payments.reject');

    Route::prefix('academy')->group(function () {
        Route::get('/', [AcademyController::class, 'index'])->name('admin.academy.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('admin.course.show');
        Route::post('/course/store', [AcademyController::class, 'storeCourse'])->name('admin.course.store');
        Route::delete('/course/{id}', [AcademyController::class, 'destroyCourse'])->name('admin.course.delete');
        Route::post('/module/store', [AcademyController::class, 'storeModule'])->name('admin.module.store');
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

    Route::get('/my-tools', [UserToolController::class, 'index'])->name('my-tools.index');
    Route::get('/my-tools/download-agent/{service_id}/{license_key}', [UserToolController::class, 'downloadAgent'])->name('my-tools.download-agent');
});

require __DIR__ . '/auth.php';
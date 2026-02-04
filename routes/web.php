<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\NotificationStreamController;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// خدمة ملفات التخزين عبر Laravel (بديل عند فشل الرابط الرمزي أو 403)
Route::get('/storage/{path}', function (string $path) {
    $path = trim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }
    $fullPath = storage_path('app/public/' . $path);
    if (!is_file($fullPath)) {
        abort(404);
    }
    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
    return response()->file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*')->name('storage.serve');

// خدمة صور الأفاتار من /avatars/ (تُخزّن فعلياً في storage/app/public/avatars/)
Route::get('/avatars/{path}', function (string $path) {
    $path = trim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }
    $fullPath = storage_path('app/public/avatars/' . $path);
    if (!is_file($fullPath)) {
        abort(404);
    }
    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
    return response()->file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*')->name('avatars.serve');

Route::get('/', [\App\Http\Controllers\Site\HomeController::class, '__invoke'])
    ->name('site.home');

Route::get('/lang/{locale}', function (string $locale) {
    $locale = strtolower(trim($locale));
    if (!in_array($locale, ['ar', 'en'], true)) {
        $locale = 'ar';
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

Route::get('/search', function () {
    return view('search');
})->name('site.search');

Route::get('/notifications', function () {
    if (!auth()->check()) {
        session(['url.intended' => route('site.notifications')]);
        return redirect(route('site.auth'));
    }
    $notifications = auth()->user()
        ->notifications()
        ->latest()
        ->paginate(20);
    $unreadCount = auth()->user()->unreadNotifications()->count();
    return view('pages.notifications', [
        'notifications' => $notifications,
        'unreadCount' => $unreadCount,
    ]);
})->name('site.notifications')->middleware('web');

Route::post('/notifications/read-all', function () {
    if (!auth()->check()) {
        return redirect(route('site.auth'));
    }
    auth()->user()->unreadNotifications->markAsRead();
    return redirect()->route('site.notifications')->with('notice', ['type' => 'success', 'message' => 'تم تعليم كل الإشعارات كمقروءة.']);
})->name('site.notifications.read-all')->middleware('web', 'auth');

Route::post('/notifications/{id}/read', function (string $id) {
    if (!auth()->check()) {
        return redirect(route('site.auth'));
    }
    $notification = auth()->user()->notifications()->find($id);
    if ($notification) {
        $notification->markAsRead();
    }
    return redirect()->back();
})->name('site.notifications.read-one')->middleware('web', 'auth');

Route::get('/account', function () {
    if (!auth()->check()) {
        session(['url.intended' => route('site.account')]);
        return redirect(route('site.auth'));
    }
    return view('pages.account');
})->name('site.account')->middleware('web');

Route::put('/account/update', function (Request $request) {
    if (!auth()->check()) {
        return redirect(route('site.auth'));
    }
    
    $user = auth()->user();
    
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'phone' => ['nullable', 'string', 'max:20'],
        'city' => ['nullable', 'string', 'max:100'],
        'job' => ['nullable', 'string', 'max:100'],
        'skills' => ['nullable', 'array'],
        'interests' => ['nullable', 'array'],
        'academic_history' => ['nullable', 'string', 'max:5000'],
        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,gif,png', 'max:2048'],
    ]);
    
    // Handle avatar upload
    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $validated['avatar'] = asset('storage/' . $avatarPath);
    }
    
    // Process skills array
    if (isset($validated['skills']) && is_string($validated['skills'])) {
        $skillsArray = array_map('trim', explode(',', $validated['skills']));
        $validated['skills'] = array_filter($skillsArray);
    }
    
    $user->update($validated);
    
    return redirect()->route('site.account')->with('notice', ['type' => 'success', 'message' => 'تم تحديث البيانات الشخصية بنجاح.']);
})->name('site.account.update')->middleware('web', 'auth');

Route::put('/account/password', function (Request $request) {
    if (!auth()->check()) {
        return redirect(route('site.auth'));
    }
    
    $user = auth()->user();
    
    $validated = $request->validate([
        'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
            if (!\Hash::check($value, $user->password)) {
                $fail('كلمة المرور الحالية غير صحيحة.');
            }
        }],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);
    
    $user->update(['password' => $validated['password']]);
    
    return redirect()->route('site.account')->with('notice', ['type' => 'success', 'message' => 'تم تحديث كلمة المرور بنجاح.']);
})->name('site.account.password')->middleware('web', 'auth');

Route::view('/subscriptions', 'pages.subscriptions')->name('site.subscriptions');
Route::view('/purchase-history', 'pages.purchase-history')->name('site.purchase-history');

Route::get('/wishlist', function () {
    $courseWishlistIds = session('course_wishlist', []);
    $courseWishlistIds = is_array($courseWishlistIds) ? array_values(array_unique(array_map('intval', $courseWishlistIds))) : [];
    $courseWishlist = collect();
    if (count($courseWishlistIds) > 0) {
        $courseWishlist = Course::query()
            ->published()
            ->whereIn('id', $courseWishlistIds)
            ->with(['instructor', 'category', 'subCategory'])
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $courseWishlistIds));
    }
    $user = auth()->user();
    $storeWishlist = collect();
    if ($user) {
        $storeWishlist = \App\Models\Wishlist::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->get();
    }
    return view('pages.wishlist', [
        'courseWishlist' => $courseWishlist,
        'storeWishlist' => $storeWishlist,
    ]);
})->name('site.wishlist');

Route::post('/wishlist/courses/{course}', function (Request $request, Course $course) {
    abort_unless((bool) $course->is_published, 404);
    $ids = session('course_wishlist', []);
    $ids = is_array($ids) ? $ids : [];
    $id = (int) $course->id;
    if (!in_array($id, array_map('intval', $ids), true)) {
        $ids[] = $id;
        $ids = array_values(array_unique(array_map('intval', $ids)));
        session(['course_wishlist' => $ids]);
    }
    return redirect()->back()->with('notice', ['type' => 'success', 'message' => 'تمت إضافة الدورة إلى قائمة الرغبات.']);
})->name('site.wishlist.courses.add');

Route::post('/wishlist/courses/{course}/remove', function (Request $request, Course $course) {
    $ids = session('course_wishlist', []);
    $ids = is_array($ids) ? $ids : [];
    $ids = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id !== (int) $course->id));
    session(['course_wishlist' => $ids]);
    return redirect()->back()->with('notice', ['type' => 'success', 'message' => 'تمت إزالة الدورة من قائمة الرغبات.']);
})->name('site.wishlist.courses.remove');

Route::get('/my-assignments', \App\Livewire\MyAssignments::class)
    ->name('site.my-assignments')
    ->middleware('auth');

Route::get('/my-courses', function () {
    if (!auth()->check()) {
        session(['url.intended' => route('site.my-courses')]);
        return redirect(route('site.auth'));
    }
    $enrollments = Enrollment::query()
        ->where('user_id', auth()->id())
        ->with(['course' => fn ($q) => $q->with(['instructor:id,name,avatar', 'category:id,name'])])
        ->orderByDesc('enrolled_at')
        ->get();

    $totalCourses = $enrollments->count();
    $completedCount = $enrollments->filter(fn ($e) => $e->completed_at !== null)->count();
    $inProgressCount = $totalCourses - $completedCount;
    $avgProgress = $totalCourses > 0
        ? round($enrollments->avg(fn ($e) => (float) ($e->progress_percentage ?? 0)), 1)
        : 0;
    $totalHours = $enrollments->sum(fn ($e) => (float) ($e->course->hours ?? 0));

    return view('pages.my-courses', [
        'enrollments' => $enrollments,
        'totalCourses' => $totalCourses,
        'completedCount' => $completedCount,
        'inProgressCount' => $inProgressCount,
        'avgProgress' => $avgProgress,
        'totalHours' => $totalHours,
    ]);
})->name('site.my-courses');
Route::get('/support', [\App\Http\Controllers\Site\SupportController::class, 'index'])->name('site.support');
Route::post('/support/complaint', [\App\Http\Controllers\Site\SupportController::class, 'storeComplaint'])->name('site.support.complaint');
Route::post('/support/contact', [\App\Http\Controllers\Site\SupportController::class, 'storeContact'])->name('site.support.contact');
Route::view('/contact', 'pages.contact')->name('site.contact');
Route::view('/about', 'pages.about')->name('site.about');

Route::get('/auth', [\App\Http\Controllers\Site\AuthController::class, 'show'])->name('site.auth');
Route::post('/auth/login', [\App\Http\Controllers\Site\AuthController::class, 'login'])->name('site.auth.login');
Route::post('/auth/register', [\App\Http\Controllers\Site\AuthController::class, 'register'])->name('site.auth.register');

Route::get('/messages', [\App\Http\Controllers\Site\MessagesController::class, 'index'])->name('site.messages');
Route::get('/messages/new', [\App\Http\Controllers\Site\MessagesController::class, 'newConversation'])->name('site.messages.new');
Route::post('/messages/start', [\App\Http\Controllers\Site\MessagesController::class, 'startConversation'])->name('site.messages.start');
Route::get('/messages/{id}', [\App\Http\Controllers\Site\MessagesController::class, 'show'])->name('site.messages.show');
Route::post('/messages/{id}/send', [\App\Http\Controllers\Site\MessagesController::class, 'send'])->name('site.messages.send');
Route::get('/instructors/{instructor}', [\App\Http\Controllers\Site\InstructorProfileController::class, 'show'])
    ->name('site.instructor.show')
    ->scopeBindings();

Route::get('/courses', function (Request $request) {
    $categoryId = (int) $request->query('category', 0);
    $subCategoryId = (int) $request->query('sub', 0);
    $instructorId = (int) $request->query('instructor', 0);
    $minRating = (float) $request->query('rating', 0);
    $priceType = (string) $request->query('price_type', '');
    $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : null;
    $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;
    $sort = (string) $request->query('sort', 'newest');

    $priceExpr = DB::raw('COALESCE(offer_price, price)');

    $query = Course::query()
        ->published()
        ->with(['instructor', 'category', 'subCategory']);

    if ($subCategoryId > 0) {
        $query->where('sub_category_id', $subCategoryId);
    } elseif ($categoryId > 0) {
        $query->where(function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                ->orWhere('sub_category_id', $categoryId);
        });
    }

    if ($instructorId > 0) {
        $query->where('user_id', $instructorId);
    }

    if ($minRating > 0) {
        $query->minRating($minRating);
    }

    if ($priceType === 'free') {
        $query->where($priceExpr, '<=', 0);
    } elseif ($priceType === 'paid') {
        $query->where($priceExpr, '>', 0);
    }

    if ($minPrice !== null) {
        $query->where($priceExpr, '>=', $minPrice);
    }
    if ($maxPrice !== null) {
        $query->where($priceExpr, '<=', $maxPrice);
    }

    // Sorting
    if ($sort === 'top') {
        $query->orderByDesc('rating')->orderByDesc('reviews_count');
    } elseif ($sort === 'price_asc') {
        $query->orderBy($priceExpr);
    } elseif ($sort === 'price_desc') {
        $query->orderByDesc($priceExpr);
    } else {
        $query->latest();
    }

    $courses = $query->paginate(12)->withQueryString();

    $categoriesTree = Category::query()
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
        ->orderBy('sort_order')
        ->get();

    $instructorIds = Course::query()
        ->published()
        ->select('user_id')
        ->distinct()
        ->pluck('user_id')
        ->filter()
        ->values()
        ->all();

    $instructors = User::query()
        ->whereIn('id', $instructorIds)
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('courses.index', [
        'courses' => $courses,
        'categoriesTree' => $categoriesTree,
        'instructors' => $instructors,
    ]);
})->name('site.courses');

Route::get('/courses/{course:slug}', function (Course $course) {
    abort_unless((bool) $course->is_published, 404);

    $course->load([
        'instructor:id,name,avatar,job,city,skills',
        'category:id,name',
        'subCategory:id,name',
        'previewLesson',
        'sections' => fn ($q) => $q->orderBy('sort_order')->with([
            'lessons' => fn ($q) => $q->orderBy('sort_order')->with(['video', 'quiz', 'files']),
        ]),
        'ratings' => fn ($q) => $q->latest()->with('user:id,name,avatar'),
    ]);

    $lessons = $course->sections->flatMap(fn ($s) => $s->lessons);
    $lessonsCount = (int) $lessons->count();
    $totalMinutes = (int) $lessons->sum(fn ($l) => (int) ($l->duration_minutes ?? 0));
    $previewLessonsCount = (int) $lessons->filter(fn ($l) => (bool) ($l->is_free ?? false) || (bool) ($l->is_free_preview ?? false))->count();

    $starsCounts = CourseRating::query()
        ->where('course_id', $course->id)
        ->selectRaw('stars, COUNT(*) as c')
        ->groupBy('stars')
        ->pluck('c', 'stars')
        ->all();

    $totalReviews = array_sum(array_map('intval', $starsCounts));
    $avgRating = (float) ($course->rating ?? 0);

    $isEnrolled = false;
    $enrollment = null;
    $userRating = null;
    if (auth()->check()) {
        $enrollment = Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();
        $isEnrolled = $enrollment !== null;
        if ($isEnrolled) {
            $userRating = CourseRating::query()
                ->where('course_id', $course->id)
                ->where('user_id', auth()->id())
                ->first();
        }
    }

    $relatedCourses = Course::query()
        ->published()
        ->where('id', '!=', $course->id)
        ->when($course->category_id, fn ($q) => $q->where('category_id', $course->category_id))
        ->with(['instructor', 'category'])
        ->orderByDesc('rating')
        ->limit(6)
        ->get();

    $firstLesson = null;
    $lessonProgressMap = [];
    $lastWatchedLesson = null;
    $recentlyCompletedLessons = [];
    $userCertificate = null;
    $nextSuggestedCourse = null;

    if ($isEnrolled && auth()->check() && $lessons->count() > 0) {
        $accessService = app(\App\Services\LessonAccessService::class);
        foreach ($lessons as $l) {
            if ($accessService->canAccessLesson(auth()->user(), $l)) {
                $firstLesson = $l;
                break;
            }
        }
        $firstLesson = $firstLesson ?? $lessons->first();

        $lessonIds = $lessons->pluck('id')->all();
        $progresses = \App\Models\VideoProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');
        foreach ($progresses as $lessonId => $vp) {
            $lessonProgressMap[$lessonId] = ['completed' => (bool) $vp->completed, 'last_position' => (int) $vp->last_position_seconds];
        }

        $lastWatched = \App\Models\VideoProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', false)
            ->orderByDesc('updated_at')
            ->first();
        if ($lastWatched) {
            $lastWatchedLesson = $lessons->firstWhere('id', $lastWatched->lesson_id);
        }

        $completedIds = \App\Models\VideoProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->pluck('lesson_id');
        $recentlyCompletedLessons = $lessons->whereIn('id', $completedIds)->values()->take(5)->all();
    }

    if ($enrollment && $enrollment->completed_at) {
        $userCertificate = \App\Models\Certificate::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();
    }

    $nextSuggestedCourse = $relatedCourses->first();
    if ($isEnrolled && auth()->check()) {
        $enrolledIds = \App\Models\Enrollment::where('user_id', auth()->id())->pluck('course_id');
        $nextSuggestedCourse = $relatedCourses->first(fn ($c) => !$enrolledIds->contains($c->id));
        $nextSuggestedCourse = $nextSuggestedCourse ?? $relatedCourses->first();
    }

    return view('courses.show', [
        'course' => $course,
        'isEnrolled' => $isEnrolled,
        'enrollment' => $enrollment,
        'userRating' => $userRating,
        'firstLesson' => $firstLesson,
        'lessonsCount' => $lessonsCount,
        'totalMinutes' => $totalMinutes,
        'previewLessonsCount' => $previewLessonsCount,
        'starsCounts' => $starsCounts,
        'totalReviews' => $totalReviews,
        'avgRating' => $avgRating,
        'relatedCourses' => $relatedCourses,
        'lessonProgressMap' => $lessonProgressMap,
        'lastWatchedLesson' => $lastWatchedLesson,
        'recentlyCompletedLessons' => $recentlyCompletedLessons,
        'userCertificate' => $userCertificate,
        'nextSuggestedCourse' => $nextSuggestedCourse,
    ]);
})->name('site.course.show');

Route::get('/courses/{course:slug}/certificate', function (Course $course) {
    abort_unless(auth()->check(), 403);
    $cert = \App\Models\Certificate::where('user_id', auth()->id())
        ->where('course_id', $course->id)
        ->firstOrFail();
    if (!$cert->pdf_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($cert->pdf_path)) {
        $service = app(\App\Services\CertificateService::class);
        $path = $service->saveCertificatePdf($cert);
        $cert->update(['pdf_path' => $path]);
    }
    return \Illuminate\Support\Facades\Storage::disk('public')->download(
        $cert->pdf_path,
        'شهادة_' . \Illuminate\Support\Str::slug($course->title) . '.pdf'
    );
})->name('site.course.certificate')->middleware('auth');

Route::get('/courses/{course:slug}/chat', \App\Livewire\CourseChat::class)
    ->name('site.course.chat')
    ->middleware('auth');

Route::post('/courses/{course:slug}/rate', [\App\Http\Controllers\Site\CourseRatingController::class, 'store'])
    ->name('site.course.rate')
    ->middleware('auth');

Route::get('/courses/{course:slug}/lessons/{lesson}', function (Course $course, \App\Models\Lesson $lesson) {
    abort_unless((bool) $course->is_published, 404);
    abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

    $course->load([
        'sections' => fn ($q) => $q->orderBy('sort_order')->with(['lessons' => fn ($q) => $q->orderBy('sort_order')]),
    ]);

    $lesson->load([
        'section.course',
        'video',
        'files',
        'quiz',
        'zoomMeeting',
        'assignments' => fn ($q) => $q->where('is_published', true),
    ]);

    $isEnrolled = false;
    $canAccess = false;
    $accessMessage = null;

    if (auth()->check()) {
        $isEnrolled = \App\Models\Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();

        if ($isEnrolled) {
            $accessService = app(\App\Services\LessonAccessService::class);
            $canAccess = $accessService->canAccessLesson(auth()->user(), $lesson);
            if (!$canAccess) {
                $incomplete = $accessService->getFirstIncompleteLesson(auth()->user(), $lesson);
                $accessMessage = $incomplete
                    ? "يجب إكمال الدرس السابق: {$incomplete->title}"
                    : 'يجب إكمال الدروس السابقة أولاً';
            }
        }
    }

    if (!$isEnrolled) {
        $canAccess = (bool) ($lesson->is_free ?? false) || (bool) ($lesson->is_free_preview ?? false);
        if (!$canAccess) {
            $accessMessage = 'يجب الاشتراك في الدورة لمشاهدة هذا الدرس';
        }
    }

    $prevLesson = null;
    $nextLesson = null;
    $allLessons = $course->sections->flatMap(fn ($s) => $s->lessons->sortBy('sort_order'))->values();
    $idx = $allLessons->search(fn ($l) => (int) $l->id === (int) $lesson->id);
    if ($idx !== false && $idx > 0) {
        $prevLesson = $allLessons[$idx - 1];
    }
    if ($idx !== false && $idx < $allLessons->count() - 1) {
        $nextLesson = $allLessons[$idx + 1];
    }

    $progress = null;
    if (auth()->check() && $isEnrolled && $canAccess) {
        $progress = \App\Models\VideoProgress::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'lesson_id' => $lesson->id,
            ],
            ['last_position_seconds' => 0, 'completed' => false, 'watch_time_minutes' => 0]
        );
    }

    return view('courses.lesson', [
        'course' => $course,
        'lesson' => $lesson,
        'canAccess' => $canAccess,
        'accessMessage' => $accessMessage,
        'isEnrolled' => $isEnrolled,
        'prevLesson' => $prevLesson,
        'nextLesson' => $nextLesson,
        'progress' => $progress,
    ]);
})->name('site.course.lesson.show');

Route::post('/courses/{course:slug}/lessons/{lesson}/save-progress', function (Request $request, Course $course, \App\Models\Lesson $lesson) {
    if (!auth()->check()) {
        return response()->json(['ok' => false], 401);
    }
    abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);
    $enrollment = \App\Models\Enrollment::query()
        ->where('user_id', auth()->id())
        ->where('course_id', $course->id)
        ->exists();
    if (!$enrollment) {
        return response()->json(['ok' => false], 403);
    }
    $accessService = app(\App\Services\LessonAccessService::class);
    if (!$accessService->canAccessLesson(auth()->user(), $lesson)) {
        return response()->json(['ok' => false], 403);
    }
    $position = (int) ($request->input('position') ?? $request->json('position') ?? 0);
    $duration = (int) ($request->input('duration') ?? $request->json('duration') ?? 0);
    $progress = \App\Models\VideoProgress::firstOrCreate(
        ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
        ['last_position_seconds' => 0, 'completed' => false, 'watch_time_minutes' => 0]
    );
    $wasCompleted = (bool) $progress->completed;
    $isCompleted = $duration > 0 && $position >= (int) ($duration * 0.9);
    $progress->update([
        'last_position_seconds' => $position,
        'last_watched_at' => now(),
        'completed' => $isCompleted,
    ]);
    if ($isCompleted && !$wasCompleted) {
        app(\App\Services\PointsService::class)->awardLessonCompleted(auth()->user(), $lesson);
    }
    $enrollmentModel = \App\Models\Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->first();
    if ($enrollmentModel) {
        $course->load(['sections' => fn ($q) => $q->withCount('lessons')]);
        $totalLessons = $course->sections->sum('lessons_count');
        $completedCount = \App\Models\VideoProgress::where('user_id', auth()->id())
            ->whereHas('lesson.section', fn ($q) => $q->where('course_id', $course->id))
            ->where('completed', true)->count();
        $enrollmentModel->update([
            'progress_percentage' => $totalLessons > 0 ? ($completedCount / $totalLessons) * 100 : 0,
            'completed_at' => $completedCount >= $totalLessons ? now() : null,
        ]);
    }
    return response()->json(['ok' => true]);
})->name('site.course.lesson.save-progress')->middleware('auth');

Route::get('/courses/{course:slug}/lessons/{lesson}/quiz', [\App\Http\Controllers\Site\QuizController::class, 'show'])->name('site.course.quiz.show');
Route::post('/courses/{course:slug}/lessons/{lesson}/quiz', [\App\Http\Controllers\Site\QuizController::class, 'submit'])->name('site.course.quiz.submit')->middleware('auth');
Route::post('/courses/{course:slug}/lessons/{lesson}/quiz/retake', [\App\Http\Controllers\Site\QuizController::class, 'retake'])->name('site.course.quiz.retake')->middleware('auth');

Route::get('/courses/{course:slug}/subscribe', function (Course $course) {
    abort_unless((bool) $course->is_published, 404);

    $course->load(['instructor:id,name', 'category:id,name', 'subCategory:id,name']);

    return view('courses.subscribe', [
        'course' => $course,
    ]);
})->name('site.course.subscribe');

Route::post('/courses/{course}/subscribe/add-to-cart', function (Request $request, Course $course) {
    abort_unless((bool) $course->is_published, 404);

    $subscriptionType = $request->input('subscription_type', 'once');
    
    $ids = session('cart', []);
    $ids = is_array($ids) ? $ids : [];
    $ids[] = (int) $course->id;
    $ids = array_values(array_unique(array_map('intval', $ids)));
    
    // Store subscription type for this course in cart
    $cartSubscriptionTypes = session('cart_subscription_types', []);
    $cartSubscriptionTypes[(int) $course->id] = $subscriptionType;
    
    session([
        'cart' => $ids,
        'cart_subscription_types' => $cartSubscriptionTypes,
    ]);

    return redirect()->route('site.cart');
})->name('site.course.subscribe.add_to_cart');

Route::post('/courses/{course}/subscribe/coupon', function (Request $request, Course $course) {
    abort_unless((bool) $course->is_published, 404);

    $code = strtoupper(trim((string) $request->input('coupon_code', '')));
    if ($code === '') {
        return redirect()
            ->route('site.course.subscribe', $course)
            ->with('notice', ['type' => 'error', 'message' => 'يرجى إدخال كود الكوبون.']);
    }

    $coupon = Coupon::query()->where('code', $code)->first();
    if (!$coupon || !$coupon->isValid()) {
        return redirect()
            ->route('site.course.subscribe', $course)
            ->with('notice', ['type' => 'error', 'message' => 'كود الكوبون غير صالح أو منتهي.']);
    }

    // Add to cart + store coupon for cart page
    $ids = session('cart', []);
    $ids = is_array($ids) ? $ids : [];
    $ids[] = (int) $course->id;
    $ids = array_values(array_unique(array_map('intval', $ids)));
    session([
        'cart' => $ids,
        'cart_coupon' => $code,
    ]);

    return redirect()->route('site.cart');
})->name('site.course.subscribe.coupon');

Route::get('/cart', function (Request $request) {
    $user = auth()->user();

    $courseCartIds = session('cart', []);
    $courseCartIds = is_array($courseCartIds) ? array_values(array_unique(array_map('intval', $courseCartIds))) : [];

    $courseCart = collect();
    if (count($courseCartIds)) {
        $courseCart = Course::query()
            ->published()
            ->whereIn('id', $courseCartIds)
            ->with(['instructor', 'category', 'subCategory'])
            ->get();
    }

    $sessionId = session()->getId();
    $storeCart = \App\Models\StoreCart::getCart($user?->id, $user ? null : $sessionId);

    $couponCode = (string) session('cart_coupon', '');
    $couponCode = strtoupper(trim($couponCode));
    $appliedCoupon = null;
    $discount = 0.0;

    if ($couponCode !== '') {
        $appliedCoupon = Coupon::query()->where('code', $couponCode)->first();
        if ($appliedCoupon && $appliedCoupon->isValid()) {
            $coursesSubtotal = (float) $courseCart->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));
            $discount = (float) $appliedCoupon->calculateDiscount($coursesSubtotal);
        } else {
            // invalid coupon in session -> clear it
            session()->forget('cart_coupon');
            $appliedCoupon = null;
            $discount = 0.0;
            $couponCode = '';
        }
    }

    return view('pages.cart', [
        'courseCart' => $courseCart,
        'storeCart' => $storeCart,
        'couponCode' => $couponCode,
        'appliedCoupon' => $appliedCoupon,
        'discount' => $discount,
    ]);
})->name('site.cart');

Route::post('/cart/coupon', function (Request $request) {
    $code = strtoupper(trim((string) $request->input('coupon_code', '')));
    if ($code === '') {
        return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'يرجى إدخال كود الكوبون.']);
    }

    $coupon = Coupon::query()->where('code', $code)->first();
    if (!$coupon || !$coupon->isValid()) {
        return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'كود الكوبون غير صالح أو منتهي.']);
    }

    session(['cart_coupon' => $code]);
    return redirect()->route('site.cart')->with('notice', ['type' => 'success', 'message' => 'تم تطبيق الكوبون بنجاح.']);
})->name('site.cart.coupon.apply');

Route::post('/cart/coupon/clear', function () {
    session()->forget('cart_coupon');
    return redirect()->route('site.cart')->with('notice', ['type' => 'success', 'message' => 'تم إزالة الكوبون.']);
})->name('site.cart.coupon.clear');

Route::get('/checkout', function () {
    if (!auth()->check()) {
        session(['url.intended' => route('site.checkout')]);
        return redirect(route('site.auth'));
    }

    $courseCartIds = session('cart', []);
    $courseCartIds = is_array($courseCartIds) ? array_values(array_unique(array_map('intval', $courseCartIds))) : [];

    $courseCart = collect();
    if (count($courseCartIds)) {
        $courseCart = Course::query()
            ->published()
            ->whereIn('id', $courseCartIds)
            ->with(['instructor', 'category', 'subCategory'])
            ->get();
    }

    if ($courseCart->count() === 0) {
        return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة. أضف دورة أولاً.']);
    }

    $couponCode = (string) session('cart_coupon', '');
    $couponCode = strtoupper(trim($couponCode));
    $appliedCoupon = null;
    $discount = 0.0;
    $subtotal = (float) $courseCart->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));

    if ($couponCode !== '') {
        $appliedCoupon = Coupon::query()->where('code', $couponCode)->first();
        if ($appliedCoupon && $appliedCoupon->isValid()) {
            $discount = (float) $appliedCoupon->calculateDiscount($subtotal);
        } else {
            session()->forget('cart_coupon');
            $appliedCoupon = null;
            $discount = 0.0;
            $couponCode = '';
        }
    }

    $total = max(0, $subtotal - $discount);

    return view('checkout.index', [
        'courseCart' => $courseCart,
        'subtotal' => $subtotal,
        'couponCode' => $couponCode,
        'discount' => $discount,
        'total' => $total,
    ]);
})->name('site.checkout');

Route::post('/checkout', function (Request $request) {
    if (!auth()->check()) {
        session(['url.intended' => route('site.checkout')]);
        return redirect(route('site.auth'));
    }

    $gateway = (string) $request->input('payment_gateway', '');
    $allowed = ['kashier', 'manual'];
    if (!in_array($gateway, $allowed, true)) {
        return redirect()->route('site.checkout')->with('notice', ['type' => 'error', 'message' => 'يرجى اختيار طريقة دفع صحيحة.']);
    }

    if ($gateway === 'manual') {
        $request->validate([
            'manual_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'manual_receipt.required' => 'يرجى إرفاق إيصال الدفع.',
            'manual_receipt.mimes' => 'صيغة الإيصال يجب أن تكون JPG/PNG/PDF.',
            'manual_receipt.max' => 'حجم الإيصال كبير جداً (الحد 5MB).',
        ]);
    }

    $courseCartIds = session('cart', []);
    $courseCartIds = is_array($courseCartIds) ? array_values(array_unique(array_map('intval', $courseCartIds))) : [];

    $courses = Course::query()
        ->published()
        ->whereIn('id', $courseCartIds)
        ->get();

    if ($courses->count() === 0) {
        return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة.']);
    }

    $subtotal = (float) $courses->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));

    $couponCode = (string) session('cart_coupon', '');
    $couponCode = strtoupper(trim($couponCode));
    $coupon = null;
    $discount = 0.0;

    if ($couponCode !== '') {
        $coupon = Coupon::query()->where('code', $couponCode)->first();
        if ($coupon && $coupon->isValid()) {
            $discount = (float) $coupon->calculateDiscount($subtotal);
        } else {
            $coupon = null;
            $discount = 0.0;
            $couponCode = '';
            session()->forget('cart_coupon');
        }
    }

    $total = max(0, $subtotal - $discount);

    try {
        DB::beginTransaction();

        $receiptPath = null;
        $receiptName = null;
        if ($gateway === 'manual' && $request->hasFile('manual_receipt')) {
            $file = $request->file('manual_receipt');
            $receiptName = $file?->getClientOriginalName();
            $receiptPath = $file?->storePublicly('manual-receipts', 'public');
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon_code' => $couponCode ?: null,
            'payment_gateway' => $gateway,
            'status' => 'pending',
            'manual_receipt_path' => $receiptPath,
            'manual_receipt_original_name' => $receiptName,
            'manual_receipt_uploaded_at' => $receiptPath ? now() : null,
        ]);

        foreach ($courses as $c) {
            $price = (float) ($c->offer_price ?? $c->price ?? 0);
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $c->id,
                'price' => $price,
            ]);

            // For manual payments: wait for admin review before granting enrollment
            if ($gateway !== 'manual') {
                Enrollment::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'course_id' => $c->id,
                    ],
                    [
                        'order_id' => $order->id,
                        'price_paid' => $price,
                        'enrolled_at' => now(),
                    ]
                );
            }
        }

        if ($coupon) {
            $coupon->increment('used_count');
        }

        DB::commit();

        session()->forget('cart');
        session()->forget('cart_coupon');

        return redirect()->route('site.checkout.success', ['order' => $order->id]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Checkout create order failed', [
            'user_id' => auth()->id(),
            'gateway' => $gateway,
            'coupon_code' => $couponCode ?: null,
            'cart_ids' => $courseCartIds,
            'error' => $e->getMessage(),
        ]);

        $message = config('app.debug')
            ? ('حدث خطأ أثناء إنشاء الطلب: ' . $e->getMessage())
            : 'حدث خطأ أثناء إنشاء الطلب. حاول مرة أخرى.';

        return redirect()->route('site.checkout')->with('notice', ['type' => 'error', 'message' => $message]);
    }
})->name('site.checkout.process');

Route::get('/checkout/success/{order}', function (Order $order) {
    if (!auth()->check()) {
        session(['url.intended' => route('site.checkout.success', ['order' => $order->id])]);
        return redirect(route('site.auth'));
    }

    abort_unless((int) $order->user_id === (int) auth()->id(), 403);

    $order->load(['items.course']);

    return view('checkout.success', [
        'order' => $order,
    ]);
})->name('site.checkout.success');

Route::post('/cart/courses/{course}', function (Request $request, Course $course) {
    abort_unless((bool) $course->is_published, 404);

    $ids = session('cart', []);
    $ids = is_array($ids) ? $ids : [];
    $ids[] = (int) $course->id;
    $ids = array_values(array_unique(array_map('intval', $ids)));
    session(['cart' => $ids]);

    return redirect()->route('site.cart');
})->name('site.cart.courses.add');

Route::post('/cart/courses/{course}/remove', function (Request $request, Course $course) {
    $ids = session('cart', []);
    $ids = is_array($ids) ? $ids : [];
    $ids = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id !== (int) $course->id));
    session(['cart' => $ids]);

    return redirect()->route('site.cart');
})->name('site.cart.courses.remove');

Route::post('/cart/courses/clear', function () {
    session()->forget('cart');
    session()->forget('cart_coupon');
    return redirect()->route('site.cart');
})->name('site.cart.courses.clear');

Route::get('/store', [\App\Http\Controllers\Site\StoreController::class, 'index'])->name('site.store');
Route::get('/store/{product:slug}', [\App\Http\Controllers\Site\StoreController::class, 'show'])->name('site.store.product');
Route::post('/store/{product:slug}/rate', [\App\Http\Controllers\Site\ProductReviewController::class, 'store'])->name('site.store.product.rate')->middleware('web');
Route::post('/store/{product}/add-to-cart', [\App\Http\Controllers\Site\StoreController::class, 'addToCart'])->name('site.store.add-to-cart');

Route::post('/wishlist/products/{product}', function (Request $request, \App\Models\Product $product) {
    if (!auth()->check()) {
        session(['url.intended' => url()->current()]);
        return redirect(route('site.auth'));
    }
    \App\Models\Wishlist::firstOrCreate(['user_id' => auth()->id(), 'product_id' => $product->id]);
    return redirect()->back()->with('notice', ['type' => 'success', 'message' => 'تمت إضافة المنتج إلى قائمة الرغبات.']);
})->name('site.wishlist.products.add');

Route::post('/wishlist/products/{product}/remove', function (Request $request, \App\Models\Product $product) {
    if (auth()->check()) {
        \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->delete();
    }
    return redirect()->back()->with('notice', ['type' => 'success', 'message' => 'تمت إزالة المنتج من قائمة الرغبات.']);
})->name('site.wishlist.products.remove');

Route::post('/cart/store/{item}', function (Request $request, \App\Models\StoreCart $item) {
    $user = auth()->user();
    $sessionId = session()->getId();
    $canRemove = ($user && (int) $item->user_id === (int) $user->id) || (!$user && $item->session_id === $sessionId);
    if ($canRemove) {
        $item->delete();
    }
    return redirect()->route('site.cart')->with('notice', ['type' => 'success', 'message' => 'تمت إزالة المنتج من السلة.']);
})->name('site.cart.store.remove');

Route::view('/blog', 'pages.blog')->name('site.blog');

// Search API routes with rate limiting
Route::prefix('api/search')->middleware(['web', 'throttle:60,1'])->group(function () {
    Route::get('/suggestions', [SearchController::class, 'suggestions'])->name('api.search.suggestions');
    Route::get('/results', [SearchController::class, 'search'])->name('api.search.results');
    Route::post('/clear-history', [SearchController::class, 'clearHistory'])->name('api.search.clear-history')->middleware('auth');
});

// Notification routes
Route::middleware(['web', 'auth'])->group(function () {
    // SSE Stream for real-time notifications
    Route::get('/notifications/stream', [NotificationStreamController::class, 'stream'])->name('notifications.stream');
    Route::get('/notifications/count', [NotificationStreamController::class, 'count'])->name('notifications.count');
    
    // Notification API
    Route::prefix('api/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
        Route::delete('/read/clear', [NotificationController::class, 'destroyRead'])->name('api.notifications.destroy-read');
    });
    
    // Messages API (recent conversations for header)
    Route::prefix('api/messages')->group(function () {
        Route::get('/unread-count', [\App\Http\Controllers\Api\MessagesController::class, 'unreadCount'])->name('api.messages.unread-count');
        Route::get('/recent', [\App\Http\Controllers\Api\MessagesController::class, 'recent'])->name('api.messages.recent');
    });

    // Reminders API
    Route::prefix('api/reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('api.reminders.index');
        Route::get('/counts', [ReminderController::class, 'counts'])->name('api.reminders.counts');
        Route::post('/dismiss', [ReminderController::class, 'dismiss'])->name('api.reminders.dismiss');
    });
});

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

Route::get('/', function () {
    $buildId = now()->format('Ymd-His');

    return response()
        ->view('home', ['__build_id' => $buildId])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0')
        ->header('X-Pegasus-Home-Build', $buildId);
});

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
        return redirect(url('/admin/login'));
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
        return redirect(url('/admin/login'));
    }
    auth()->user()->unreadNotifications->markAsRead();
    return redirect()->route('site.notifications')->with('notice', ['type' => 'success', 'message' => 'تم تعليم كل الإشعارات كمقروءة.']);
})->name('site.notifications.read-all')->middleware('web', 'auth');

Route::post('/notifications/{id}/read', function (string $id) {
    if (!auth()->check()) {
        return redirect(url('/admin/login'));
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
        return redirect(url('/admin/login'));
    }
    return view('pages.account');
})->name('site.account')->middleware('web');

Route::put('/account/update', function (Request $request) {
    if (!auth()->check()) {
        return redirect(url('/admin/login'));
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
        return redirect(url('/admin/login'));
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

Route::get('/my-courses', function () {
    if (!auth()->check()) {
        session(['url.intended' => route('site.my-courses')]);
        return redirect(url('/admin/login'));
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
Route::view('/support', 'pages.support')->name('site.support');
Route::view('/about', 'pages.about')->name('site.about');
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
        'sections' => fn ($q) => $q->orderBy('sort_order')->with([
            'lessons' => fn ($q) => $q->orderBy('sort_order'),
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
    if (auth()->check()) {
        $isEnrolled = Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();
    }

    $relatedCourses = Course::query()
        ->published()
        ->where('id', '!=', $course->id)
        ->when($course->category_id, fn ($q) => $q->where('category_id', $course->category_id))
        ->with(['instructor', 'category'])
        ->orderByDesc('rating')
        ->limit(6)
        ->get();

    return view('courses.show', [
        'course' => $course,
        'lessonsCount' => $lessonsCount,
        'totalMinutes' => $totalMinutes,
        'previewLessonsCount' => $previewLessonsCount,
        'starsCounts' => $starsCounts,
        'totalReviews' => $totalReviews,
        'avgRating' => $avgRating,
        'isEnrolled' => $isEnrolled,
        'relatedCourses' => $relatedCourses,
    ]);
})->name('site.course.show');

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
        return redirect(url('/admin/login'));
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
        return redirect(url('/admin/login'));
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
        return redirect(url('/admin/login'));
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

Route::view('/store', 'pages.store')->name('site.store');
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
    
    // Reminders API
    Route::prefix('api/reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index'])->name('api.reminders.index');
        Route::get('/counts', [ReminderController::class, 'counts'])->name('api.reminders.counts');
        Route::post('/dismiss', [ReminderController::class, 'dismiss'])->name('api.reminders.dismiss');
    });
});

/// عنوان الـ API الأساسي للربط مع الباكند (Laravel).
/// الافتراضي: الدومين الرسمي أكاديمية بيغاسوس.
/// للتطوير المحلي: flutter run --dart-define=API_BASE_URL=http://عنوان_الخادم
const String apiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'https://academypegasus.com',
);

/// مسار تسجيل الدخول
const String apiAuthLogin = '/api/auth/login';

/// مسار إنشاء حساب
const String apiAuthRegister = '/api/auth/register';

/// مسار تسجيل الخروج
const String apiAuthLogout = '/api/auth/logout';

/// مسار بيانات المستخدم وتحديثه (GET / PUT)
const String apiAuthUser = '/api/auth/user';

/// تغيير كلمة المرور
const String apiAuthPassword = '/api/auth/password';

/// الإشعارات (يتطلب مصادقة)
const String apiNotifications = '/api/notifications';
const String apiNotificationsUnreadCount = '/api/notifications/unread-count';
const String apiNotificationsRead = '/api/notifications';
const String apiNotificationsReadAll = '/api/notifications/read-all';
const String apiNotificationsDestroyRead = '/api/notifications/read/clear';

/// الرسائل (يتطلب مصادقة)
const String apiMessagesRecent = '/api/messages/recent';
const String apiMessagesUnreadCount = '/api/messages/unread-count';
const String apiMessagesConversation = '/api/messages/conversations';
const String apiMessagesStart = '/api/messages/start';
const String apiMessagesUsers = '/api/messages/users';

/// الصفحة الرئيسية (دورات مميزة + أحدث الدورات)
const String apiHome = '/api/home';

/// تصنيفات المتجر (من إدارة المتجر)
const String apiStoreCategories = '/api/store/categories';

/// قائمة منتجات المتجر (حسب التصنيف: category, sub)
const String apiStoreProducts = '/api/store/products';

/// تفاصيل منتج واحد
const String apiStoreProductDetail = '/api/store/product';
const String apiStoreProductRate = '/api/store/products';
const String apiStoreProductReviews = '/api/store/products';

/// المفضلة (دورات + منتجات، يتطلب مصادقة)
const String apiWishlist = '/api/wishlist';

/// السلة (دورات + منتجات، يتطلب مصادقة)
const String apiCart = '/api/cart';

/// الدفع (معاينة + إتمام)
const String apiCheckoutPreview = '/api/checkout/preview';
const String apiCheckoutProcess = '/api/checkout';
const String apiCheckoutValidateCoupon = '/api/checkout/validate-coupon';

/// تصنيفات الدورات (من إدارة الدورات التدريبية)
const String apiCoursesCategories = '/api/courses/categories';

/// قائمة الدورات (حسب التصنيف: category, sub)
const String apiCoursesList = '/api/courses';

/// دوراتي (المسجل فيها — يتطلب مصادقة)
const String apiMyCourses = '/api/my-courses';

/// واجباتي (يتطلب مصادقة)
const String apiMyAssignments = '/api/my-assignments';

/// سجل الطلبات/المشتريات (يتطلب مصادقة)
const String apiOrders = '/api/orders';
const String apiStoreOrders = '/api/store-orders';

/// الاشتراكات (خطط + اشتراكاتي + اشتراك)
const String apiSubscriptionsPlans = '/api/subscriptions/plans';
const String apiSubscriptionsMy = '/api/subscriptions/my';
const String apiSubscriptionsSubscribe = '/api/subscriptions/subscribe';

/// إعدادات الدعم (بريد، هواتف)
const String apiSupport = '/api/support';
const String apiSupportComplaint = '/api/support/complaint';
const String apiSupportContact = '/api/support/contact';

/// دروس الدورة (يتطلب مصادقة)
const String apiLessonShow = '/api/courses'; // {slug}/lessons/{id}
const String apiLessonSaveProgress = '/api/courses'; // {slug}/lessons/{id}/save-progress

/// اختبار الدرس (يتطلب مصادقة)
const String apiQuizShow = '/api/courses'; // {slug}/lessons/{id}/quiz
const String apiQuizSubmit = '/api/courses'; // {slug}/lessons/{id}/quiz
const String apiQuizRetake = '/api/courses'; // {slug}/lessons/{id}/quiz/retake

/// شهادة الدورة (يتطلب مصادقة)
const String apiCertificate = '/api/courses'; // {slug}/certificate

/// تقييم الدورة (يتطلب مصادقة)
const String apiCourseRate = '/api/courses'; // {slug}/rate

/// البحث
const String apiSearchSuggestions = '/api/search/suggestions';
const String apiSearchResults = '/api/search/results';
const String apiSearchClearHistory = '/api/search/clear-history';

/// التنبيهات (يتطلب مصادقة)
const String apiReminders = '/api/reminders';

/// المدونة
const String apiBlog = '/api/blog';
const String apiRemindersCounts = '/api/reminders/counts';
const String apiRemindersDismiss = '/api/reminders/dismiss';

/// الملف الشخصي للمدرب
const String apiInstructors = '/api/instructors';

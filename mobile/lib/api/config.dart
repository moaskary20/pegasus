/// عنوان الـ API الأساسي للربط مع الباكند (Laravel).
/// غيّره حسب بيئة التشغيل (تطوير / إنتاج).
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

/// مسار بيانات المستخدم
const String apiAuthUser = '/api/auth/user';

/// الإشعارات (يتطلب مصادقة)
const String apiNotifications = '/api/notifications';
const String apiNotificationsUnreadCount = '/api/notifications/unread-count';
const String apiNotificationsRead = '/api/notifications';
const String apiNotificationsReadAll = '/api/notifications/read-all';
const String apiNotificationsDestroyRead = '/api/notifications/read/clear';

/// الرسائل (يتطلب مصادقة)
const String apiMessagesRecent = '/api/messages/recent';
const String apiMessagesUnreadCount = '/api/messages/unread-count';

/// الصفحة الرئيسية (دورات مميزة + أحدث الدورات)
const String apiHome = '/api/home';

/// تصنيفات المتجر (من إدارة المتجر)
const String apiStoreCategories = '/api/store/categories';

/// قائمة منتجات المتجر (حسب التصنيف: category, sub)
const String apiStoreProducts = '/api/store/products';

/// تفاصيل منتج واحد
const String apiStoreProductDetail = '/api/store/product';

/// المفضلة (دورات + منتجات، يتطلب مصادقة)
const String apiWishlist = '/api/wishlist';

/// السلة (دورات + منتجات، يتطلب مصادقة)
const String apiCart = '/api/cart';

/// تصنيفات الدورات (من إدارة الدورات التدريبية)
const String apiCoursesCategories = '/api/courses/categories';

/// قائمة الدورات (حسب التصنيف: category, sub)
const String apiCoursesList = '/api/courses';

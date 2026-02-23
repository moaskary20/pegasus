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

import 'dart:async';
import 'dart:io';

/// تحويل أخطاء API والشبكة لرسائل واضحة للمستخدم بالعربية
class ErrorMessages {
  ErrorMessages._();

  /// رسالة مناسبة للمستخدم بناءً على نوع الخطأ أو رمز الحالة
  static String from(dynamic error, {int? statusCode, String? fallback}) {
    // أولوية لرمز الحالة إن وُجد
    if (statusCode != null) {
      final statusMsg = _fromStatusCode(statusCode);
      if (statusMsg != null) return statusMsg;
    }

    if (error is SocketException) {
      return 'تحقق من اتصالك بالإنترنت وحاول مرة أخرى';
    }
    if (error is TimeoutException) {
      return 'انتهت مهلة الاتصال. تأكد من سرعة الإنترنت وحاول مجدداً';
    }
    if (error is HandshakeException) {
      return 'تعذر الاتصال بشكل آمن. تحقق من اتصال الإنترنت';
    }
    if (error is HttpException) {
      return 'تعذر الاتصال بالخادم. حاول لاحقاً';
    }
    if (error is FormatException) {
      return 'خطأ في البيانات المستلمة. حاول تحديث الصفحة';
    }

    final fall = fallback ?? 'حدث خطأ غير متوقع. حاول مرة أخرى';
    return fall;
  }

  static String? _fromStatusCode(int code) {
    switch (code) {
      case 400:
        return 'طلب غير صحيح. تحقق من البيانات المُدخلة';
      case 401:
        return 'انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى';
      case 403:
        return 'ليس لديك صلاحية لهذا الإجراء';
      case 404:
        return 'المحتوى غير متوفر أو تم حذفه';
      case 422:
        return 'البيانات المُدخلة غير صحيحة. راجع الحقول وتأكد منها';
      case 429:
        return 'تجاوزت الحد المسموح. انتظر قليلاً وحاول مرة أخرى';
      case 500:
      case 502:
      case 503:
        return 'عطل مؤقت في الخادم. حاول بعد لحظات';
      default:
        if (code >= 500) return 'خطأ من الخادم. حاول لاحقاً';
        if (code >= 400) return 'حدث خطأ ($code). حاول مرة أخرى';
        return null;
    }
  }

  /// رسالة لشاشات التحميل عند فشل الشبكة
  static String networkError() =>
      'تحقق من اتصالك بالإنترنت وحاول التحديث';

  /// رسالة عند انتهاء الجلسة (401)
  static String sessionExpired() =>
      'انتهت الجلسة. يرجى تسجيل الدخول مرة أخرى';

  /// رسالة عامة لخطأ غير معروف
  static String generic() =>
      'حدث خطأ. جرّب التحديث أو العودة لاحقاً';
}

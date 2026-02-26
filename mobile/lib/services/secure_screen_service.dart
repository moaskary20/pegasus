import 'package:flutter/services.dart';

/// تفعيل أو إلغاء منع تصوير الشاشة (FLAG_SECURE) بناءً على إعدادات الـ backend
class SecureScreenService {
  SecureScreenService._();

  static const _channel = MethodChannel('com.pegasus.academy/secure');

  /// تفعيل أو إيقاف منع تصوير الشاشة
  static Future<void> setSecureFlag(bool enable) async {
    try {
      await _channel.invokeMethod('setSecureFlag', enable);
    } on PlatformException catch (_) {
      // تجاهل إذا المنصة لا تدعم (مثلاً ويب)
    }
  }
}

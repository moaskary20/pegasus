import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class AppSettingsApi {
  AppSettingsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب إعدادات التطبيق من الـ backend
  static Future<AppSettings?> getSettings() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiAppSettings');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
        return AppSettings.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class AppSettings {
  AppSettings({
    this.preventScreenCapture = true,
    this.maxDevicesPerAccount = 3,
    this.maxViewsPerLesson = 10,
    this.enforceLessonOrder = true,
    this.requireLessonCompletion = 80,
    this.enableVideoWatermark = false,
    this.watermarkText = '',
    this.watermarkTextResolved = '',
    this.preventVideoDownload = true,
    this.enablePlaybackSpeed = true,
    this.enableVideoResume = true,
    this.maxFailedLoginAttempts = 5,
  });

  final bool preventScreenCapture;
  final int maxDevicesPerAccount;
  final int maxViewsPerLesson;
  final bool enforceLessonOrder;
  final int requireLessonCompletion;
  final bool enableVideoWatermark;
  final String watermarkText;
  final String watermarkTextResolved;
  final bool preventVideoDownload;
  final bool enablePlaybackSpeed;
  final bool enableVideoResume;
  final int maxFailedLoginAttempts;

  factory AppSettings.fromJson(Map<String, dynamic> json) {
    return AppSettings(
      preventScreenCapture: (json['prevent_screen_capture'] as bool?) ?? true,
      maxDevicesPerAccount: (json['max_devices_per_account'] as num?)?.toInt() ?? 3,
      maxViewsPerLesson: (json['max_views_per_lesson'] as num?)?.toInt() ?? 10,
      enforceLessonOrder: (json['enforce_lesson_order'] as bool?) ?? true,
      requireLessonCompletion: (json['require_lesson_completion'] as num?)?.toInt() ?? 80,
      enableVideoWatermark: (json['enable_video_watermark'] as bool?) ?? false,
      watermarkText: (json['watermark_text'] ?? '').toString(),
      watermarkTextResolved: (json['watermark_text_resolved'] ?? '').toString(),
      preventVideoDownload: (json['prevent_video_download'] as bool?) ?? true,
      enablePlaybackSpeed: (json['enable_playback_speed'] as bool?) ?? true,
      enableVideoResume: (json['enable_video_resume'] as bool?) ?? true,
      maxFailedLoginAttempts: (json['max_failed_login_attempts'] as num?)?.toInt() ?? 5,
    );
  }
}

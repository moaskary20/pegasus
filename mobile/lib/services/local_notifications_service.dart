import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest.dart' as tz_data;
import 'package:timezone/timezone.dart' as tz;

/// خدمة التنبيهات المحلية — تذكير بوقت الدرس أو الاختبار
class LocalNotificationsService {
  LocalNotificationsService._();

  static final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  static bool _initialized = false;

  /// تهيئة الخدمة (يُستدعى من main)
  static Future<void> init() async {
    if (_initialized) return;

    tz_data.initializeTimeZones();
    try {
      tz.setLocalLocation(tz.getLocation('Africa/Cairo'));
    } catch (_) {
      try {
        tz.setLocalLocation(tz.getLocation('UTC'));
      } catch (_) {}
    }

    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
    );
    const settings = InitializationSettings(
      android: android,
      iOS: ios,
    );

    await _plugin.initialize(
      settings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );
    _initialized = true;
  }

  static void _onNotificationTapped(NotificationResponse response) {
    // يمكن ربط التنقل للدورة/الدرس عند الضغط على الإشعار
    final payload = response.payload;
    if (payload != null && payload.isNotEmpty) {
      // payload يمكن أن يكون: "course:slug" أو "lesson:slug:id"
      // يتم التعامل معه في MainApp أو عبر deep link
    }
  }

  /// طلب الأذونات (Android 13+)
  static Future<bool> requestPermissions() async {
    final androidPlugin =
        _plugin.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    if (androidPlugin != null) {
      return await androidPlugin.requestNotificationsPermission() ?? false;
    }
    final iosPlugin =
        _plugin.resolvePlatformSpecificImplementation<IOSFlutterLocalNotificationsPlugin>();
    if (iosPlugin != null) {
      return await iosPlugin.requestPermissions(alert: true, badge: true) ?? false;
    }
    return true;
  }

  /// جدولة تذكير بعد دقائق
  static Future<void> scheduleReminder({
    required int id,
    required String title,
    required String body,
    required int minutesFromNow,
    String? payload,
  }) async {
    await requestPermissions();

    final now = DateTime.now();
    final scheduled = now.add(Duration(minutes: minutesFromNow));

    const androidDetails = AndroidNotificationDetails(
      'reminders',
      'تذكيرات الدروس',
      channelDescription: 'تذكير بوقت الدرس أو الاختبار',
      importance: Importance.defaultImportance,
      priority: Priority.defaultPriority,
    );
    const iosDetails = DarwinNotificationDetails();

    await _plugin.zonedSchedule(
      id,
      title,
      body,
      tz.TZDateTime.from(scheduled, tz.local),
      NotificationDetails(android: androidDetails, iOS: iosDetails),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      payload: payload,
    );
  }

  /// إلغاء تذكير
  static Future<void> cancel(int id) async {
    await _plugin.cancel(id);
  }

  /// إلغاء جميع التذكيرات
  static Future<void> cancelAll() async {
    await _plugin.cancelAll();
  }
}

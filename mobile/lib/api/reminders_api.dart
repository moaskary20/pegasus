import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class RemindersApi {
  RemindersApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<RemindersResponse> getReminders({String? type}) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) {
        return RemindersResponse(reminders: [], needsAuth: true);
      }
      var uri = Uri.parse('$apiBaseUrl$apiReminders');
      if (type != null && type.isNotEmpty) {
        uri = uri.replace(queryParameters: {'type': type});
      }
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return RemindersResponse(reminders: [], needsAuth: true);
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['reminders'] as List<dynamic>?) ?? [];
        final reminders = list
            .map((e) => ReminderItem.fromJson(e as Map<String, dynamic>))
            .toList();
        return RemindersResponse(reminders: reminders, needsAuth: false);
      }
      return RemindersResponse(reminders: [], needsAuth: false);
    } catch (_) {
      return RemindersResponse(reminders: [], needsAuth: false);
    }
  }

  static Future<ReminderCountsResponse> getCounts() async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) {
        return ReminderCountsResponse(counts: {}, needsAuth: true);
      }
      final uri = Uri.parse('$apiBaseUrl$apiRemindersCounts');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return ReminderCountsResponse(counts: {}, needsAuth: true);
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final counts = <String, int>{};
        data.forEach((k, v) {
          counts[k.toString()] = (v as num?)?.toInt() ?? 0;
        });
        return ReminderCountsResponse(counts: counts, needsAuth: false);
      }
      return ReminderCountsResponse(counts: {}, needsAuth: false);
    } catch (_) {
      return ReminderCountsResponse(counts: {}, needsAuth: false);
    }
  }

  static Future<bool> dismiss({required String type, int? remindableId}) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) return false;
      final uri = Uri.parse('$apiBaseUrl$apiRemindersDismiss');
      final body = <String, dynamic>{'type': type};
      if (remindableId != null) body['remindable_id'] = remindableId;
      final res = await http.post(
        uri,
        headers: {..._headers, 'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class ReminderItem {
  ReminderItem({
    required this.type,
    required this.title,
    required this.message,
    this.icon,
    this.color,
    this.actionUrl,
    this.actionLabel,
    this.remindableType,
    this.remindableId,
    this.courseSlug,
    this.lessonId,
  });

  final String type;
  final String title;
  final String message;
  final String? icon;
  final String? color;
  final String? actionUrl;
  final String? actionLabel;
  final String? remindableType;
  final int? remindableId;
  /// للموبايل: للتنقل إلى الدورة أو الاختبار
  final String? courseSlug;
  /// للموبايل: للتنقل إلى اختبار الدرس (نوع quiz فقط)
  final int? lessonId;

  factory ReminderItem.fromJson(Map<String, dynamic> json) {
    return ReminderItem(
      type: (json['type'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      icon: json['icon']?.toString(),
      color: json['color']?.toString(),
      actionUrl: json['action_url']?.toString(),
      actionLabel: json['action_label']?.toString(),
      remindableType: json['remindable_type']?.toString(),
      remindableId: (json['remindable_id'] as num?)?.toInt(),
      courseSlug: json['course_slug']?.toString(),
      lessonId: (json['lesson_id'] as num?)?.toInt(),
    );
  }
}

class RemindersResponse {
  RemindersResponse({required this.reminders, this.needsAuth = false});
  final List<ReminderItem> reminders;
  final bool needsAuth;
}

class ReminderCountsResponse {
  ReminderCountsResponse({required this.counts, this.needsAuth = false});
  final Map<String, int> counts;
  final bool needsAuth;
}

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class ReminderSettingsApi {
  ReminderSettingsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json', 'Content-Type': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب إعدادات التنبيهات
  static Future<List<ReminderSettingItem>> getSettings() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiReminderSettings');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['data'] as List<dynamic>?) ?? [];
        return list.map((e) => ReminderSettingItem.fromJson(e as Map<String, dynamic>)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  /// تحديث إعدادات التنبيهات
  static Future<bool> updateSettings(List<Map<String, dynamic>> settings) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiReminderSettings');
      final res = await http.put(
        uri,
        headers: _headers,
        body: jsonEncode({'settings': settings}),
      );
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class ReminderSettingItem {
  ReminderSettingItem({
    required this.type,
    required this.label,
    required this.icon,
    required this.enabled,
    required this.emailEnabled,
  });

  final String type;
  final String label;
  final String icon;
  final bool enabled;
  final bool emailEnabled;

  factory ReminderSettingItem.fromJson(Map<String, dynamic> json) {
    return ReminderSettingItem(
      type: (json['type'] ?? '').toString(),
      label: (json['label'] ?? '').toString(),
      icon: (json['icon'] ?? '🔔').toString(),
      enabled: (json['enabled'] as bool?) ?? true,
      emailEnabled: (json['email_enabled'] as bool?) ?? true,
    );
  }
}

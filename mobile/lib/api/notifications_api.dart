import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class NotificationsApi {
  NotificationsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json', 'Content-Type': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<NotificationsResponse> getNotifications({
    int page = 1,
    int perPage = 15,
    bool? unreadOnly,
  }) async {
    try {
      var path = '$apiBaseUrl$apiNotifications?page=$page&per_page=$perPage';
      if (unreadOnly == true) path += '&unread=1';
      final uri = Uri.parse(path);
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return NotificationsResponse(
          notifications: [],
          currentPage: 1,
          lastPage: 1,
          total: 0,
          unreadCount: 0,
          needsAuth: true,
        );
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['notifications'] as List<dynamic>?) ?? [];
        final meta = data['meta'] as Map<String, dynamic>? ?? {};
        final notifications = list
            .map((e) => AppNotification.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList();
        return NotificationsResponse(
          notifications: notifications,
          currentPage: (meta['current_page'] as num?)?.toInt() ?? 1,
          lastPage: (meta['last_page'] as num?)?.toInt() ?? 1,
          total: (meta['total'] as num?)?.toInt() ?? 0,
          unreadCount: (meta['unread_count'] as num?)?.toInt() ?? 0,
          needsAuth: false,
        );
      }
      return NotificationsResponse(notifications: [], currentPage: 1, lastPage: 1, total: 0, unreadCount: 0, needsAuth: false);
    } catch (_) {
      return NotificationsResponse(notifications: [], currentPage: 1, lastPage: 1, total: 0, unreadCount: 0, needsAuth: false);
    }
  }

  static Future<int> getUnreadCount() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiNotificationsUnreadCount');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) return (data['count'] as num?)?.toInt() ?? 0;
      return 0;
    } catch (e) {
      return 0;
    }
  }

  static Future<bool> markAsRead(String id) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiNotifications/$id/read');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  static Future<bool> markAllAsRead() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiNotificationsReadAll');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  static Future<bool> deleteRead() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiNotificationsDestroyRead');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  static Future<bool> delete(String id) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiNotifications/$id');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (e) {
      return false;
    }
  }
}

class AppNotification {
  AppNotification({
    required this.id,
    required this.type,
    this.data,
    this.readAt,
    required this.createdAt,
  });

  final String id;
  final String type;
  final Map<String, dynamic>? data;
  final String? readAt;
  final String createdAt;

  bool get isRead => readAt != null && readAt!.isNotEmpty;

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: (json['id'] ?? json['id']?.toString() ?? '').toString(),
      type: (json['type'] ?? '').toString(),
      data: json['data'] as Map<String, dynamic>?,
      readAt: json['read_at']?.toString(),
      createdAt: (json['created_at'] ?? '').toString(),
    );
  }

  String get title => data?['title']?.toString() ?? type;
  String get body => data?['message']?.toString() ?? data?['body']?.toString() ?? '';
}

class NotificationsResponse {
  NotificationsResponse({
    required this.notifications,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.unreadCount,
    this.needsAuth = false,
  });
  final List<AppNotification> notifications;
  final int currentPage;
  final int lastPage;
  final int total;
  final int unreadCount;
  final bool needsAuth;
}

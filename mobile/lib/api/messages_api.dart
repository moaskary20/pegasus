import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class MessagesApi {
  MessagesApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json', 'Content-Type': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<MessagesRecentResponse> getRecent({int limit = 20}) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiMessagesRecent?limit=$limit');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['conversations'] as List<dynamic>?)?.cast<Map<String, dynamic>>() ?? [];
        return MessagesRecentResponse(
          conversations: list.map((e) => ConversationPreview.fromJson(e)).toList(),
          unreadCount: (data['unread_count'] as num?)?.toInt() ?? 0,
        );
      }
      return MessagesRecentResponse(conversations: [], unreadCount: 0);
    } catch (_) {
      return MessagesRecentResponse(conversations: [], unreadCount: 0);
    }
  }

  static Future<int> getUnreadCount() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiMessagesUnreadCount');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) return (data['count'] as num?)?.toInt() ?? 0;
      return 0;
    } catch (e) {
      return 0;
    }
  }
}

class ConversationPreview {
  ConversationPreview({
    required this.id,
    required this.name,
    required this.lastPreview,
    this.lastAt,
    required this.unread,
    this.url,
  });

  final int id;
  final String name;
  final String lastPreview;
  final String? lastAt;
  final int unread;
  final String? url;

  factory ConversationPreview.fromJson(Map<String, dynamic> json) {
    return ConversationPreview(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      lastPreview: (json['last_preview'] ?? '').toString(),
      lastAt: json['last_at']?.toString(),
      unread: (json['unread'] as num?)?.toInt() ?? 0,
      url: json['url']?.toString(),
    );
  }
}

class MessagesRecentResponse {
  MessagesRecentResponse({required this.conversations, required this.unreadCount});
  final List<ConversationPreview> conversations;
  final int unreadCount;
}

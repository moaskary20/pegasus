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
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiMessagesRecent?limit=$limit');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['conversations'] as List<dynamic>?)?.cast<Map<String, dynamic>>() ?? [];
        return MessagesRecentResponse(
          conversations: list.map((e) => ConversationPreview.fromJson(e)).toList(),
          unreadCount: (data['unread_count'] as num?)?.toInt() ?? 0,
          needsAuth: false,
          networkError: false,
        );
      }
      return MessagesRecentResponse(
        conversations: [],
        unreadCount: 0,
        needsAuth: res.statusCode == 401,
        networkError: false,
      );
    } catch (e) {
      return MessagesRecentResponse(
        conversations: [],
        unreadCount: 0,
        needsAuth: false,
        networkError: true,
      );
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

  /// عرض محادثة مع رسائلها
  static Future<ConversationDetail?> getConversation(int conversationId) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) return null;
      final uri = Uri.parse('$apiBaseUrl$apiMessagesConversation/$conversationId');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return ConversationDetail.fromJson(data);
    } catch (_) {
      return null;
    }
  }

  /// إرسال رسالة
  static Future<SendMessageResult?> sendMessage(int conversationId, String body) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) return null;
      final uri = Uri.parse('$apiBaseUrl$apiMessagesConversation/$conversationId/send');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({'body': body}),
      );
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      final msg = data['message'] as Map<String, dynamic>?;
      return msg != null ? SendMessageResult.fromJson(data) : null;
    } catch (_) {
      return null;
    }
  }

  /// بدء محادثة مع مستخدم
  static Future<StartConversationResult?> startConversation(int userId) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) return null;
      final uri = Uri.parse('$apiBaseUrl$apiMessagesStart');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({'user_id': userId}),
      );
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return StartConversationResult.fromJson(data);
    } catch (_) {
      return null;
    }
  }

  /// البحث عن مستخدمين
  static Future<List<MessageUser>> searchUsers(String query) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null || query.length < 2) return [];
      final uri = Uri.parse('$apiBaseUrl$apiMessagesUsers?q=${Uri.encodeComponent(query)}');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode != 200) return [];
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      final list = (data['users'] as List<dynamic>?) ?? [];
      return list.map((e) => MessageUser.fromJson(e as Map<String, dynamic>)).toList();
    } catch (_) {
      return [];
    }
  }
}

class ConversationDetail {
  ConversationDetail({
    required this.conversation,
    required this.messages,
  });

  final ConversationInfo conversation;
  final List<ChatMessage> messages;

  factory ConversationDetail.fromJson(Map<String, dynamic> json) {
    final convData = json['conversation'] as Map<String, dynamic>? ?? {};
    final messagesRaw = (json['messages'] as List<dynamic>?) ?? [];
    return ConversationDetail(
      conversation: ConversationInfo.fromJson(convData),
      messages: messagesRaw
          .map((e) => ChatMessage.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class ConversationInfo {
  ConversationInfo({
    required this.id,
    required this.name,
    required this.type,
    this.otherUser,
  });

  final int id;
  final String name;
  final String type;
  final OtherUser? otherUser;

  factory ConversationInfo.fromJson(Map<String, dynamic> json) {
    final other = json['other_user'] as Map<String, dynamic>?;
    return ConversationInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      type: (json['type'] ?? 'private').toString(),
      otherUser: other != null ? OtherUser.fromJson(other) : null,
    );
  }
}

class OtherUser {
  OtherUser({required this.id, required this.name, this.avatar});

  final int id;
  final String name;
  final String? avatar;

  factory OtherUser.fromJson(Map<String, dynamic> json) {
    return OtherUser(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      avatar: json['avatar']?.toString(),
    );
  }
}

class ChatMessage {
  ChatMessage({
    required this.id,
    required this.userId,
    required this.body,
    required this.type,
    required this.createdAt,
    required this.isMine,
    this.senderName,
    this.attachments,
  });

  final int id;
  final int userId;
  final String body;
  final String type;
  final String createdAt;
  final bool isMine;
  final String? senderName;
  final List<MessageAttachment>? attachments;

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    final attRaw = (json['attachments'] as List<dynamic>?) ?? [];
    return ChatMessage(
      id: (json['id'] as num?)?.toInt() ?? 0,
      userId: (json['user_id'] as num?)?.toInt() ?? 0,
      body: (json['body'] ?? '').toString(),
      type: (json['type'] ?? 'text').toString(),
      createdAt: (json['created_at'] ?? '').toString(),
      isMine: json['is_mine'] == true,
      senderName: json['sender_name']?.toString(),
      attachments: attRaw
          .map((e) => MessageAttachment.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class MessageAttachment {
  MessageAttachment({required this.id, required this.fileName, required this.url, this.fileType});

  final int id;
  final String fileName;
  final String url;
  final String? fileType;

  factory MessageAttachment.fromJson(Map<String, dynamic> json) {
    return MessageAttachment(
      id: (json['id'] as num?)?.toInt() ?? 0,
      fileName: (json['file_name'] ?? '').toString(),
      url: (json['url'] ?? '').toString(),
      fileType: json['file_type']?.toString(),
    );
  }
}

class SendMessageResult {
  SendMessageResult({required this.success, required this.message});

  final bool success;
  final ChatMessage message;

  factory SendMessageResult.fromJson(Map<String, dynamic> json) {
    final msg = json['message'] as Map<String, dynamic>? ?? {};
    return SendMessageResult(
      success: json['success'] == true,
      message: ChatMessage.fromJson(msg),
    );
  }
}

class StartConversationResult {
  StartConversationResult({required this.success, required this.conversationId, this.conversation});

  final bool success;
  final int conversationId;
  final ConversationInfo? conversation;

  factory StartConversationResult.fromJson(Map<String, dynamic> json) {
    final convData = json['conversation'] as Map<String, dynamic>?;
    return StartConversationResult(
      success: json['success'] == true,
      conversationId: (json['conversation_id'] as num?)?.toInt() ?? 0,
      conversation: convData != null ? ConversationInfo.fromJson(convData) : null,
    );
  }
}

class MessageUser {
  MessageUser({required this.id, required this.name, this.avatar});

  final int id;
  final String name;
  final String? avatar;

  factory MessageUser.fromJson(Map<String, dynamic> json) {
    return MessageUser(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      avatar: json['avatar']?.toString(),
    );
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
  MessagesRecentResponse({
    required this.conversations,
    required this.unreadCount,
    this.needsAuth = false,
    this.networkError = false,
  });
  final List<ConversationPreview> conversations;
  final int unreadCount;
  final bool needsAuth;
  final bool networkError;
}

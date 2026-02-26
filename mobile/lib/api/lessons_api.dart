import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class LessonsApi {
  LessonsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب تفاصيل الدرس
  static Future<LessonDetailItem?> getLesson(String courseSlug, int lessonId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/lessons/$lessonId');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
        return LessonDetailItem.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// حفظ تقدم المشاهدة
  static Future<bool> saveProgress(String courseSlug, int lessonId, int position, int duration) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/lessons/$lessonId/save-progress');
      final headers = {..._headers, 'Content-Type': 'application/json'};
      final body = jsonEncode({'position': position, 'duration': duration});
      final res = await http.post(uri, headers: headers, body: body);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class LessonDetailItem {
  LessonDetailItem({
    required this.id,
    required this.title,
    this.videoUrl,
    required this.durationMinutes,
    required this.isFreePreview,
    this.prevLesson,
    this.nextLesson,
    required this.canAccess,
    required this.hasQuiz,
    this.content,
    this.contentType,
    this.files = const [],
    this.zoomMeeting,
  });

  final int id;
  final String title;
  final String? videoUrl;
  final int durationMinutes;
  final bool isFreePreview;
  final PrevNextLesson? prevLesson;
  final PrevNextLesson? nextLesson;
  final bool canAccess;
  final bool hasQuiz;
  final String? content;
  final String? contentType;
  final List<LessonFileItem> files;
  final LessonZoomMeeting? zoomMeeting;

  factory LessonDetailItem.fromJson(Map<String, dynamic> json) {
    PrevNextLesson? prev;
    PrevNextLesson? next;
    if (json['prev_lesson'] != null) {
      final p = json['prev_lesson'] as Map<String, dynamic>;
      prev = PrevNextLesson(id: (p['id'] as num?)?.toInt() ?? 0, title: (p['title'] ?? '').toString());
    }
    if (json['next_lesson'] != null) {
      final n = json['next_lesson'] as Map<String, dynamic>;
      next = PrevNextLesson(id: (n['id'] as num?)?.toInt() ?? 0, title: (n['title'] ?? '').toString());
    }
    final filesRaw = (json['files'] as List<dynamic>?) ?? [];
    final files = filesRaw.map((e) => LessonFileItem.fromJson(e as Map<String, dynamic>)).toList();
    LessonZoomMeeting? zoom;
    if (json['zoom_meeting'] != null) {
      zoom = LessonZoomMeeting.fromJson(json['zoom_meeting'] as Map<String, dynamic>);
    }
    return LessonDetailItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      videoUrl: json['video_url']?.toString(),
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      isFreePreview: (json['is_free_preview'] as bool?) ?? false,
      prevLesson: prev,
      nextLesson: next,
      canAccess: (json['can_access'] as bool?) ?? true,
      hasQuiz: (json['has_quiz'] as bool?) ?? false,
      content: json['content']?.toString(),
      contentType: json['content_type']?.toString(),
      files: files,
      zoomMeeting: zoom,
    );
  }
}

class LessonFileItem {
  LessonFileItem({required this.id, required this.name, this.url, required this.size});
  final int id;
  final String name;
  final String? url;
  final int size;
  factory LessonFileItem.fromJson(Map<String, dynamic> json) => LessonFileItem(
        id: (json['id'] as num?)?.toInt() ?? 0,
        name: (json['name'] ?? '').toString(),
        url: json['url']?.toString(),
        size: (json['size'] as num?)?.toInt() ?? 0,
      );
}

class LessonZoomMeeting {
  LessonZoomMeeting({
    this.joinUrl,
    this.scheduledStartTime,
    int? duration,
    this.topic,
  }) : duration = duration ?? 0;
  final String? joinUrl;
  final String? scheduledStartTime;
  final int duration;
  final String? topic;
  factory LessonZoomMeeting.fromJson(Map<String, dynamic> json) => LessonZoomMeeting(
        joinUrl: json['join_url']?.toString(),
        scheduledStartTime: json['scheduled_start_time']?.toString(),
        duration: (json['duration'] as num?)?.toInt(),
        topic: json['topic']?.toString(),
      );
}

class PrevNextLesson {
  PrevNextLesson({required this.id, required this.title});
  final int id;
  final String title;
}

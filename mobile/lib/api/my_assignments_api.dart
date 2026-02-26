import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class MyAssignmentsApi {
  MyAssignmentsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<MyAssignmentsResponse> getMyAssignments() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiMyAssignments');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['assignments'] as List<dynamic>?) ?? [];
        final stats = (data['stats'] as Map<String, dynamic>?) ?? {};
        return MyAssignmentsResponse(
          assignments: list.map((e) => MyAssignmentItem.fromJson(e as Map<String, dynamic>)).toList(),
          total: (stats['total'] as num?)?.toInt() ?? 0,
          pending: (stats['pending'] as num?)?.toInt() ?? 0,
          submitted: (stats['submitted'] as num?)?.toInt() ?? 0,
          graded: (stats['graded'] as num?)?.toInt() ?? 0,
          needsAuth: false,
        );
      }
      return MyAssignmentsResponse(
        assignments: [],
        total: 0,
        pending: 0,
        submitted: 0,
        graded: 0,
        needsAuth: res.statusCode == 401,
      );
    } catch (_) {
      return MyAssignmentsResponse(
        assignments: [],
        total: 0,
        pending: 0,
        submitted: 0,
        graded: 0,
        needsAuth: false,
      );
    }
  }
}

class MyAssignmentItem {
  MyAssignmentItem({
    required this.id,
    required this.title,
    required this.type,
    this.dueDate,
    required this.maxScore,
    required this.courseId,
    required this.courseTitle,
    required this.courseSlug,
    this.lessonTitle,
    required this.status,
    this.lastSubmissionStatus,
    this.score,
  });

  final int id;
  final String title;
  final String type;
  final String? dueDate;
  final int maxScore;
  final int courseId;
  final String courseTitle;
  final String courseSlug;
  final String? lessonTitle;
  final String status;
  final String? lastSubmissionStatus;
  final num? score;

  factory MyAssignmentItem.fromJson(Map<String, dynamic> json) {
    return MyAssignmentItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      type: (json['type'] ?? 'assignment').toString(),
      dueDate: json['due_date']?.toString(),
      maxScore: (json['max_score'] as num?)?.toInt() ?? 0,
      courseId: (json['course_id'] as num?)?.toInt() ?? 0,
      courseTitle: (json['course_title'] ?? '').toString(),
      courseSlug: (json['course_slug'] ?? '').toString(),
      lessonTitle: json['lesson_title']?.toString(),
      status: (json['status'] ?? 'pending').toString(),
      lastSubmissionStatus: json['last_submission_status']?.toString(),
      score: json['score'] as num?,
    );
  }
}

class MyAssignmentsResponse {
  MyAssignmentsResponse({
    required this.assignments,
    required this.total,
    required this.pending,
    required this.submitted,
    required this.graded,
    this.needsAuth = false,
  });
  final List<MyAssignmentItem> assignments;
  final int total;
  final int pending;
  final int submitted;
  final int graded;
  final bool needsAuth;
}

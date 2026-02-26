import 'dart:convert';
import 'package:file_picker/file_picker.dart';
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

  static Future<AssignmentDetailResponse?> getAssignmentDetail(int id) async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiMyAssignments/$id');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return AssignmentDetailResponse.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  static Future<AssignmentSubmitResult> submitAssignment(
    int assignmentId, {
    required String content,
    required List<PlatformFile> files,
  }) async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiMyAssignments/$assignmentId/submit');
      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll(_headers);
      request.fields['content'] = content;
      for (final f in files) {
        if (f.path != null && f.path!.isNotEmpty) {
          request.files.add(await http.MultipartFile.fromPath(
            'files[]',
            f.path!,
            filename: f.name,
          ));
        } else if (f.bytes != null && f.bytes!.isNotEmpty) {
          request.files.add(http.MultipartFile.fromBytes(
            'files[]',
            f.bytes!,
            filename: f.name,
          ));
        }
      }
      final streamed = await request.send();
      final res = await http.Response.fromStream(streamed);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 201) {
        return AssignmentSubmitResult(
          success: true,
          message: (data['message'] ?? 'تم التسليم بنجاح').toString(),
          submissionId: (data['submission_id'] as num?)?.toInt(),
        );
      }
      return AssignmentSubmitResult(
        success: false,
        message: (data['message'] ?? 'حدث خطأ').toString(),
      );
    } catch (e) {
      return AssignmentSubmitResult(success: false, message: 'تحقق من الاتصال بالإنترنت');
    }
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

class AssignmentDetailResponse {
  AssignmentDetailResponse({
    required this.id,
    required this.title,
    required this.description,
    required this.instructions,
    required this.type,
    required this.maxScore,
    this.dueDate,
    required this.isOverdue,
    required this.allowedFileTypes,
    required this.maxFileSizeMb,
    required this.canSubmit,
    required this.courseTitle,
    required this.courseSlug,
    this.lessonTitle,
    this.lastSubmission,
  });
  final int id;
  final String title;
  final String description;
  final String instructions;
  final String type;
  final int maxScore;
  final String? dueDate;
  final bool isOverdue;
  final List<dynamic> allowedFileTypes;
  final double maxFileSizeMb;
  final bool canSubmit;
  final String courseTitle;
  final String courseSlug;
  final String? lessonTitle;
  final LastSubmissionInfo? lastSubmission;

  factory AssignmentDetailResponse.fromJson(Map<String, dynamic> json) {
    final last = json['last_submission'] as Map<String, dynamic>?;
    return AssignmentDetailResponse(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      instructions: (json['instructions'] ?? '').toString(),
      type: (json['type'] ?? 'assignment').toString(),
      maxScore: (json['max_score'] as num?)?.toInt() ?? 0,
      dueDate: json['due_date']?.toString(),
      isOverdue: json['is_overdue'] == true,
      allowedFileTypes: (json['allowed_file_types'] as List<dynamic>?) ?? [],
      maxFileSizeMb: (json['max_file_size_mb'] as num?)?.toDouble() ?? 10,
      canSubmit: json['can_submit'] == true,
      courseTitle: (json['course_title'] ?? '').toString(),
      courseSlug: (json['course_slug'] ?? '').toString(),
      lessonTitle: json['lesson_title']?.toString(),
      lastSubmission: last != null ? LastSubmissionInfo.fromJson(last) : null,
    );
  }
}

class LastSubmissionInfo {
  LastSubmissionInfo({
    required this.id,
    required this.status,
    this.score,
    this.feedback,
    this.submittedAt,
    required this.files,
  });
  final int id;
  final String status;
  final num? score;
  final String? feedback;
  final String? submittedAt;
  final List<SubmissionFileInfo> files;

  factory LastSubmissionInfo.fromJson(Map<String, dynamic> json) {
    final files = (json['files'] as List<dynamic>?) ?? [];
    return LastSubmissionInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      status: (json['status'] ?? '').toString(),
      score: json['score'] as num?,
      feedback: json['feedback']?.toString(),
      submittedAt: json['submitted_at']?.toString(),
      files: files.map((e) => SubmissionFileInfo.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class SubmissionFileInfo {
  SubmissionFileInfo({required this.id, required this.fileName, required this.filePath});
  final int id;
  final String fileName;
  final String filePath;
  factory SubmissionFileInfo.fromJson(Map<String, dynamic> json) {
    return SubmissionFileInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      fileName: (json['file_name'] ?? '').toString(),
      filePath: (json['file_path'] ?? '').toString(),
    );
  }
}

class AssignmentSubmitResult {
  AssignmentSubmitResult({required this.success, required this.message, this.submissionId});
  final bool success;
  final String message;
  final int? submissionId;
}

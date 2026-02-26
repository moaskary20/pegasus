import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class QuizApi {
  QuizApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب الاختبار (مع إنشاء محاولة إن لزم). يرجع النتيجة أو رسالة الخطأ من الباكند.
  static Future<QuizLoadResult> getQuiz(String courseSlug, int lessonId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/lessons/$lessonId/quiz');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        if (data['max_reached'] == true) {
          return QuizLoadResult(
            response: QuizResponse(
              maxReached: true,
              quiz: null,
              attempt: null,
              lastAttempt: data['last_attempt'] != null
                  ? QuizAttemptResult.fromJson(data['last_attempt'] as Map<String, dynamic>)
                  : null,
            ),
          );
        }
        if (data['already_submitted'] == true) {
          final quizJson = data['quiz'];
          return QuizLoadResult(
            response: QuizResponse(
              maxReached: false,
              alreadySubmitted: true,
              quiz: quizJson != null ? QuizDetail.fromJson(quizJson as Map<String, dynamic>) : null,
              attempt: null,
              lastAttempt: data['attempt'] != null
                  ? QuizAttemptResult.fromJson(data['attempt'] as Map<String, dynamic>)
                  : null,
            ),
          );
        }
        return QuizLoadResult(
          response: QuizResponse(
            maxReached: false,
            alreadySubmitted: false,
            quiz: QuizDetail.fromJson(data['quiz'] as Map<String, dynamic>),
            attempt: data['attempt'] != null
                ? QuizAttemptData.fromJson(data['attempt'] as Map<String, dynamic>)
                : null,
            lastAttempt: null,
          ),
        );
      }
      final msg = (data['message'] ?? data['error'] ?? '').toString().toLowerCase();
      String error = 'تعذر تحميل الاختبار';
      if (res.statusCode == 401) {
        error = 'يجب تسجيل الدخول لأداء الاختبار';
      } else if (res.statusCode == 403) {
        error = msg.contains('enrolled') ? 'يجب التسجيل في الدورة لأداء الاختبار' : 'غير مسموح';
      } else if (res.statusCode == 404) {
        if (msg.contains('no quiz') || msg.contains('quiz')) {
          error = 'لا يوجد اختبار لهذا الدرس';
        } else if (msg.contains('lesson')) {
          error = 'الدرس غير موجود';
        } else if (msg.contains('course')) {
          error = 'الدورة غير موجودة';
        } else {
          error = 'لا يوجد اختبار لهذا الدرس';
        }
      } else if (msg.isNotEmpty) {
        error = (data['message'] ?? data['error'] ?? error).toString();
      }
      return QuizLoadResult(error: error);
    } catch (e) {
      return QuizLoadResult(error: 'خطأ في الاتصال. تحقق من الإنترنت وحاول مرة أخرى.');
    }
  }

  /// إرسال الإجابات
  static Future<QuizSubmitResult?> submitQuiz(String courseSlug, int lessonId, Map<int, dynamic> answers) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/lessons/$lessonId/quiz');
      final headers = {..._headers, 'Content-Type': 'application/json'};
      final answersMap = answers.map((k, v) => MapEntry(k.toString(), v));
      final res = await http.post(uri, headers: headers, body: jsonEncode({'answers': answersMap}));
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return QuizSubmitResult(
          score: (data['score'] as num?)?.toDouble() ?? 0,
          passed: (data['passed'] as bool?) ?? false,
          passPercentage: (data['pass_percentage'] as num?)?.toDouble() ?? 0,
          attempt: data['attempt'] != null
              ? QuizAttemptResult.fromJson(data['attempt'] as Map<String, dynamic>)
              : null,
        );
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// إعادة المحاولة
  static Future<QuizRetakeResult?> retakeQuiz(String courseSlug, int lessonId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/lessons/$lessonId/quiz/retake');
      final res = await http.post(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return QuizRetakeResult(
          success: (data['success'] as bool?) ?? true,
          attempt: data['attempt'] != null
              ? QuizAttemptData.fromJson(data['attempt'] as Map<String, dynamic>)
              : null,
        );
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class QuizLoadResult {
  QuizLoadResult({this.response, this.error});
  final QuizResponse? response;
  final String? error;
}

class QuizResponse {
  QuizResponse({
    this.maxReached = false,
    this.alreadySubmitted = false,
    this.quiz,
    this.attempt,
    this.lastAttempt,
  });
  final bool maxReached;
  final bool alreadySubmitted;
  final QuizDetail? quiz;
  final QuizAttemptData? attempt;
  final QuizAttemptResult? lastAttempt;
}

class QuizDetail {
  QuizDetail({
    required this.id,
    required this.title,
    required this.durationMinutes,
    required this.passPercentage,
    required this.allowRetake,
    this.maxAttempts,
    required this.questions,
  });
  final int id;
  final String title;
  final int durationMinutes;
  final double passPercentage;
  final bool allowRetake;
  final int? maxAttempts;
  final List<QuizQuestionItem> questions;

  factory QuizDetail.fromJson(Map<String, dynamic> json) {
    final qRaw = (json['questions'] as List<dynamic>?) ?? [];
    return QuizDetail(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      passPercentage: (json['pass_percentage'] as num?)?.toDouble() ?? 0,
      allowRetake: (json['allow_retake'] as bool?) ?? false,
      maxAttempts: json['max_attempts'] != null ? (json['max_attempts'] as num).toInt() : null,
      questions: qRaw.map((e) => QuizQuestionItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class QuizQuestionItem {
  QuizQuestionItem({
    required this.id,
    required this.type,
    required this.questionText,
    this.options,
    required this.points,
  });
  final int id;
  final String type;
  final String questionText;
  final List<dynamic>? options;
  final double points;

  factory QuizQuestionItem.fromJson(Map<String, dynamic> json) {
    return QuizQuestionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      type: (json['type'] ?? 'mcq').toString(),
      questionText: (json['question_text'] ?? '').toString(),
      options: json['options'] as List<dynamic>?,
      points: (json['points'] as num?)?.toDouble() ?? 1,
    );
  }
}

class QuizAttemptData {
  QuizAttemptData({
    required this.id,
    required this.attemptNumber,
    this.startedAt,
    this.timeRemainingSeconds,
    this.answers = const {},
  });
  final int id;
  final int attemptNumber;
  final String? startedAt;
  final int? timeRemainingSeconds;
  final Map<String, dynamic> answers;

  factory QuizAttemptData.fromJson(Map<String, dynamic> json) {
    final a = json['answers'];
    Map<String, dynamic> answersMap = {};
    if (a is Map) {
      answersMap = Map<String, dynamic>.from(a);
    }
    return QuizAttemptData(
      id: (json['id'] as num?)?.toInt() ?? 0,
      attemptNumber: (json['attempt_number'] as num?)?.toInt() ?? 1,
      startedAt: json['started_at']?.toString(),
      timeRemainingSeconds: json['time_remaining_seconds'] != null ? (json['time_remaining_seconds'] as num).toInt() : null,
      answers: answersMap,
    );
  }
}

class QuizAttemptResult {
  QuizAttemptResult({
    required this.id,
    this.score,
    required this.passed,
    this.submittedAt,
    this.attemptNumber,
  });
  final int id;
  final double? score;
  final bool passed;
  final String? submittedAt;
  final int? attemptNumber;

  factory QuizAttemptResult.fromJson(Map<String, dynamic> json) {
    return QuizAttemptResult(
      id: (json['id'] as num?)?.toInt() ?? 0,
      score: json['score'] != null ? (json['score'] as num).toDouble() : null,
      passed: (json['passed'] as bool?) ?? false,
      submittedAt: json['submitted_at']?.toString(),
      attemptNumber: json['attempt_number'] != null ? (json['attempt_number'] as num).toInt() : null,
    );
  }
}

class QuizSubmitResult {
  QuizSubmitResult({
    required this.score,
    required this.passed,
    required this.passPercentage,
    this.attempt,
  });
  final double score;
  final bool passed;
  final double passPercentage;
  final QuizAttemptResult? attempt;
}

class QuizRetakeResult {
  QuizRetakeResult({required this.success, this.attempt});
  final bool success;
  final QuizAttemptData? attempt;
}

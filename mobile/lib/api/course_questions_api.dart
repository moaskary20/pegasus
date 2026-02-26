import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class CourseQuestionsApi {
  CourseQuestionsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static String get _base => apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;

  /// جلب أسئلة وأجوبة الدرس
  static Future<List<CourseQuestionItem>?> getQuestions(String courseSlug, int lessonId) async {
    try {
      final uri = Uri.parse('$_base/api/courses/$courseSlug/lessons/$lessonId/questions');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
        final list = data['questions'] as List<dynamic>? ?? [];
        return list.map((e) => CourseQuestionItem.fromJson(e as Map<String, dynamic>)).toList();
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// إضافة سؤال جديد
  static Future<CourseQuestionItem?> addQuestion(String courseSlug, int lessonId, String question) async {
    try {
      final uri = Uri.parse('$_base/api/courses/$courseSlug/lessons/$lessonId/questions');
      final headers = {..._headers, 'Content-Type': 'application/json'};
      final body = jsonEncode({'question': question});
      final res = await http.post(uri, headers: headers, body: body);
      if (res.statusCode == 201) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
        return CourseQuestionItem.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class CourseQuestionItem {
  CourseQuestionItem({
    required this.id,
    required this.question,
    required this.userName,
    required this.isAnswered,
    this.createdAt,
    this.answers = const [],
  });

  final int id;
  final String question;
  final String userName;
  final bool isAnswered;
  final String? createdAt;
  final List<CourseAnswerItem> answers;

  factory CourseQuestionItem.fromJson(Map<String, dynamic> json) {
    final answersList = json['answers'] as List<dynamic>? ?? [];
    return CourseQuestionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      question: (json['question'] ?? '').toString(),
      userName: (json['user_name'] ?? '').toString(),
      isAnswered: (json['is_answered'] as bool?) ?? false,
      createdAt: json['created_at']?.toString(),
      answers: answersList.map((e) => CourseAnswerItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class CourseAnswerItem {
  CourseAnswerItem({
    required this.id,
    required this.answer,
    required this.userName,
    this.createdAt,
  });

  final int id;
  final String answer;
  final String userName;
  final String? createdAt;

  factory CourseAnswerItem.fromJson(Map<String, dynamic> json) {
    return CourseAnswerItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      answer: (json['answer'] ?? '').toString(),
      userName: (json['user_name'] ?? '').toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}

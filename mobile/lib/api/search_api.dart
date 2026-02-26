import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';

class SearchApi {
  SearchApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// اقتراحات البحث أثناء الكتابة (recent, popular, courses من الباكند)
  static Future<SearchSuggestionsResult> getSuggestions(String query) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSearchSuggestions').replace(queryParameters: {'q': query});
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString());
      if (res.statusCode == 200 && data is Map<String, dynamic>) {
        String toStr(dynamic e) => e is Map ? (e['text'] ?? e['query'] ?? '').toString() : e.toString();
        final recent = ((data['recent'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final popular = ((data['popular'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final courses = ((data['courses'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final suggestions = [...popular, ...courses];
        return SearchSuggestionsResult(suggestions: suggestions, recent: recent);
      }
    } catch (_) {}
    return SearchSuggestionsResult(suggestions: [], recent: []);
  }

  /// نتائج البحث (دورات، دروس، مدربين، أسئلة)
  static Future<SearchResultsResponse> search(
    String query, {
    int? categoryId,
    String? level,
    bool? isFree,
    double? minRating,
    int? instructorId,
    String? sort,
  }) async {
    try {
      final params = <String, String>{'q': query};
      if (categoryId != null && categoryId > 0) params['category_id'] = categoryId.toString();
      if (level != null && level.isNotEmpty) params['level'] = level;
      if (isFree != null) params['is_free'] = isFree.toString();
      if (minRating != null && minRating > 0) params['min_rating'] = minRating.toString();
      if (instructorId != null && instructorId > 0) params['instructor_id'] = instructorId.toString();
      if (sort != null && sort.isNotEmpty) params['sort'] = sort;

      final uri = Uri.parse('$apiBaseUrl$apiSearchResults').replace(queryParameters: params);
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final results = data['results'] as Map<String, dynamic>? ?? {};
        final coursesRaw = (results['courses'] ?? data['courses']) as List<dynamic>? ?? [];
        final lessonsRaw = results['lessons'] as List<dynamic>? ?? [];
        final instructorsRaw = results['instructors'] as List<dynamic>? ?? [];
        final questionsRaw = results['questions'] as List<dynamic>? ?? [];

        final courses = coursesRaw.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList();
        final lessons = lessonsRaw.map((e) => SearchLessonItem.fromJson(e as Map<String, dynamic>)).toList();
        final instructors = instructorsRaw.map((e) => SearchInstructorItem.fromJson(e as Map<String, dynamic>)).toList();
        final questions = questionsRaw.map((e) => SearchQuestionItem.fromJson(e as Map<String, dynamic>)).toList();

        return SearchResultsResponse(
          query: (data['query'] ?? query).toString(),
          courses: courses,
          lessons: lessons,
          instructors: instructors,
          questions: questions,
        );
      }
    } catch (_) {}
    return SearchResultsResponse(query: query, courses: [], lessons: [], instructors: [], questions: []);
  }

  /// مسح سجل البحث (يتطلب مصادقة)
  static Future<bool> clearHistory() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSearchClearHistory');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class SearchSuggestionsResult {
  SearchSuggestionsResult({required this.suggestions, required this.recent});
  final List<String> suggestions;
  final List<String> recent;
}

class SearchResultsResponse {
  SearchResultsResponse({
    required this.query,
    required this.courses,
    required this.lessons,
    required this.instructors,
    required this.questions,
  });
  final String query;
  final List<CourseItem> courses;
  final List<SearchLessonItem> lessons;
  final List<SearchInstructorItem> instructors;
  final List<SearchQuestionItem> questions;

  bool get hasResults =>
      courses.isNotEmpty || lessons.isNotEmpty || instructors.isNotEmpty || questions.isNotEmpty;
}

class SearchLessonItem {
  SearchLessonItem({
    required this.id,
    required this.title,
    this.description,
    required this.courseId,
    this.courseSlug,
    required this.courseTitle,
    this.instructor,
    this.sectionTitle,
    this.durationMinutes,
    this.isFree = false,
  });
  final int id;
  final String title;
  final String? description;
  final int courseId;
  final String? courseSlug;
  final String courseTitle;
  final String? instructor;
  final String? sectionTitle;
  final int? durationMinutes;
  final bool isFree;

  factory SearchLessonItem.fromJson(Map<String, dynamic> json) {
    return SearchLessonItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      description: json['description']?.toString(),
      courseId: (json['course_id'] as num?)?.toInt() ?? 0,
      courseSlug: json['course_slug']?.toString(),
      courseTitle: (json['course_title'] ?? '').toString(),
      instructor: json['instructor']?.toString(),
      sectionTitle: json['section_title']?.toString(),
      durationMinutes: (json['duration_minutes'] as num?)?.toInt(),
      isFree: (json['is_free'] as bool?) ?? false,
    );
  }
}

class SearchInstructorItem {
  SearchInstructorItem({
    required this.id,
    required this.name,
    this.avatar,
    this.job,
    this.city,
    this.coursesCount = 0,
  });
  final int id;
  final String name;
  final String? avatar;
  final String? job;
  final String? city;
  final int coursesCount;

  factory SearchInstructorItem.fromJson(Map<String, dynamic> json) {
    return SearchInstructorItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      avatar: json['avatar']?.toString(),
      job: json['job']?.toString(),
      city: json['city']?.toString(),
      coursesCount: (json['courses_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class SearchQuestionItem {
  SearchQuestionItem({
    required this.id,
    required this.question,
    required this.courseId,
    this.courseSlug,
    required this.courseTitle,
    this.lessonId,
    this.lessonTitle,
    required this.userName,
    this.answersCount = 0,
    required this.createdAt,
  });
  final int id;
  final String question;
  final int courseId;
  final String? courseSlug;
  final String courseTitle;
  final int? lessonId;
  final String? lessonTitle;
  final String userName;
  final int answersCount;
  final String createdAt;

  factory SearchQuestionItem.fromJson(Map<String, dynamic> json) {
    return SearchQuestionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      question: (json['question'] ?? '').toString(),
      courseId: (json['course_id'] as num?)?.toInt() ?? 0,
      courseSlug: json['course_slug']?.toString(),
      courseTitle: (json['course_title'] ?? '').toString(),
      lessonId: (json['lesson_id'] as num?)?.toInt(),
      lessonTitle: json['lesson_title']?.toString(),
      userName: (json['user_name'] ?? '').toString(),
      answersCount: (json['answers_count'] as num?)?.toInt() ?? 0,
      createdAt: (json['created_at'] ?? '').toString(),
    );
  }
}

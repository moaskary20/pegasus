import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class MyCoursesApi {
  MyCoursesApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<MyCoursesResponse> getMyCourses() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiMyCourses');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['enrollments'] as List<dynamic>?) ?? [];
        return MyCoursesResponse(
          enrollments: list.map((e) => MyEnrollmentItem.fromJson(e as Map<String, dynamic>)).toList(),
          totalCourses: (data['total_courses'] as num?)?.toInt() ?? 0,
          completedCount: (data['completed_count'] as num?)?.toInt() ?? 0,
          inProgressCount: (data['in_progress_count'] as num?)?.toInt() ?? 0,
          avgProgress: (data['avg_progress'] as num?)?.toDouble() ?? 0,
          totalHours: (data['total_hours'] as num?)?.toDouble() ?? 0,
          needsAuth: false,
        );
      }
      return MyCoursesResponse(
        enrollments: [],
        totalCourses: 0,
        completedCount: 0,
        inProgressCount: 0,
        avgProgress: 0,
        totalHours: 0,
        needsAuth: res.statusCode == 401,
      );
    } catch (_) {
      return MyCoursesResponse(
        enrollments: [],
        totalCourses: 0,
        completedCount: 0,
        inProgressCount: 0,
        avgProgress: 0,
        totalHours: 0,
        needsAuth: false,
      );
    }
  }
}

class MyEnrollmentItem {
  MyEnrollmentItem({
    required this.id,
    required this.courseId,
    required this.title,
    required this.slug,
    this.coverImage,
    required this.instructorName,
    required this.categoryName,
    required this.hours,
    required this.progressPercentage,
    this.enrolledAt,
    this.completedAt,
    this.subscriptionType,
    this.accessExpiresAt,
  });

  final int id;
  final int courseId;
  final String title;
  final String slug;
  final String? coverImage;
  final String instructorName;
  final String categoryName;
  final double hours;
  final double progressPercentage;
  final String? enrolledAt;
  final String? completedAt;
  final String? subscriptionType;
  final String? accessExpiresAt;

  factory MyEnrollmentItem.fromJson(Map<String, dynamic> json) {
    return MyEnrollmentItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      courseId: (json['course_id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      coverImage: json['cover_image']?.toString(),
      instructorName: (json['instructor_name'] ?? '').toString(),
      categoryName: (json['category_name'] ?? '').toString(),
      hours: (json['hours'] as num?)?.toDouble() ?? 0,
      progressPercentage: (json['progress_percentage'] as num?)?.toDouble() ?? 0,
      enrolledAt: json['enrolled_at']?.toString(),
      completedAt: json['completed_at']?.toString(),
      subscriptionType: json['subscription_type']?.toString(),
      accessExpiresAt: json['access_expires_at']?.toString(),
    );
  }
}

class MyCoursesResponse {
  MyCoursesResponse({
    required this.enrollments,
    required this.totalCourses,
    required this.completedCount,
    required this.inProgressCount,
    required this.avgProgress,
    required this.totalHours,
    this.needsAuth = false,
  });
  final List<MyEnrollmentItem> enrollments;
  final int totalCourses;
  final int completedCount;
  final int inProgressCount;
  final double avgProgress;
  final double totalHours;
  final bool needsAuth;
}

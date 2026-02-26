import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';

class InstructorApi {
  InstructorApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<InstructorProfile?> getInstructor(int instructorId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiInstructors/$instructorId');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return InstructorProfile.fromJson(data);
    } catch (_) {
      return null;
    }
  }
}

class InstructorProfile {
  InstructorProfile({
    required this.instructor,
    required this.courses,
    required this.coursesCount,
    required this.totalStudents,
  });

  final InstructorInfo instructor;
  final List<CourseListItem> courses;
  final int coursesCount;
  final int totalStudents;

  factory InstructorProfile.fromJson(Map<String, dynamic> json) {
    final instructorData = json['instructor'] as Map<String, dynamic>? ?? {};
    final coursesRaw = (json['courses'] as List<dynamic>?) ?? [];
    return InstructorProfile(
      instructor: InstructorInfo.fromJson(instructorData),
      courses: coursesRaw
          .map((e) => CourseListItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      coursesCount: (json['courses_count'] as num?)?.toInt() ?? 0,
      totalStudents: (json['total_students'] as num?)?.toInt() ?? 0,
    );
  }
}

class InstructorInfo {
  InstructorInfo({
    required this.id,
    required this.name,
    this.avatar,
    this.bio,
  });

  final int id;
  final String name;
  final String? avatar;
  final String? bio;

  factory InstructorInfo.fromJson(Map<String, dynamic> json) {
    return InstructorInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      avatar: json['avatar']?.toString(),
      bio: json['bio']?.toString(),
    );
  }
}

class CourseListItem {
  CourseListItem({
    required this.id,
    required this.title,
    required this.slug,
    this.coverImage,
    required this.price,
    this.originalPrice,
    this.rating,
    this.reviewsCount,
    this.studentsCount,
    this.category,
  });

  final int id;
  final String title;
  final String slug;
  final String? coverImage;
  final double price;
  final double? originalPrice;
  final double? rating;
  final int? reviewsCount;
  final int? studentsCount;
  final CategoryRef? category;

  factory CourseListItem.fromJson(Map<String, dynamic> json) {
    return CourseListItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      coverImage: json['cover_image']?.toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      originalPrice: json['original_price'] != null
          ? (json['original_price'] as num).toDouble()
          : null,
      rating: json['rating'] != null ? (json['rating'] as num).toDouble() : null,
      reviewsCount: (json['reviews_count'] as num?)?.toInt(),
      studentsCount: (json['students_count'] as num?)?.toInt(),
      category: json['category'] != null
          ? CategoryRef.fromJson(json['category'] as Map<String, dynamic>)
          : null,
    );
  }
}

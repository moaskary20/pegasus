import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class HomeApi {
  HomeApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<HomeResponse> getHome() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiHome');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final top = (data['top_courses'] as List<dynamic>?) ?? [];
        final recent = (data['recent_courses'] as List<dynamic>?) ?? [];
        final wishlistIds = (data['wishlist_ids'] as List<dynamic>?)?.cast<int>() ?? [];
        final categoriesRaw = (data['categories'] as List<dynamic>?) ?? [];
        final categories = categoriesRaw.map((e) {
          final m = e as Map<String, dynamic>;
          final coursesList = (m['courses'] as List<dynamic>?) ?? [];
          return CategoryWithCourses(
            id: (m['id'] as num?)?.toInt() ?? 0,
            name: (m['name'] ?? '').toString(),
            slug: (m['slug'] ?? '').toString(),
            publishedCoursesCount: (m['published_courses_count'] as num?)?.toInt() ?? 0,
            courses: coursesList.map((c) => CourseItem.fromJson(c as Map<String, dynamic>)).toList(),
          );
        }).toList();
        return HomeResponse(
          topCourses: top.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList(),
          recentCourses: recent.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList(),
          categories: categories,
          wishlistIds: wishlistIds,
        );
      }
      return HomeResponse(topCourses: [], recentCourses: [], categories: [], wishlistIds: []);
    } catch (e) {
      return HomeResponse(topCourses: [], recentCourses: [], categories: [], wishlistIds: []);
    }
  }
}

class HomeResponse {
  HomeResponse({
    required this.topCourses,
    required this.recentCourses,
    required this.categories,
    required this.wishlistIds,
  });
  final List<CourseItem> topCourses;
  final List<CourseItem> recentCourses;
  final List<CategoryWithCourses> categories;
  final List<int> wishlistIds;
}

class CategoryWithCourses {
  CategoryWithCourses({
    required this.id,
    required this.name,
    required this.slug,
    required this.publishedCoursesCount,
    required this.courses,
  });
  final int id;
  final String name;
  final String slug;
  final int publishedCoursesCount;
  final List<CourseItem> courses;
}

class CourseItem {
  CourseItem({
    required this.id,
    required this.title,
    required this.slug,
    required this.price,
    this.originalPrice,
    required this.rating,
    required this.reviewsCount,
    required this.studentsCount,
    this.coverImage,
    this.category,
    this.instructor,
  });

  final int id;
  final String title;
  final String slug;
  final double price;
  final double? originalPrice;
  final double rating;
  final int reviewsCount;
  final int studentsCount;
  final String? coverImage;
  final CategoryRef? category;
  final InstructorRef? instructor;

  bool get hasDiscount => originalPrice != null && originalPrice! > price;

  factory CourseItem.fromJson(Map<String, dynamic> json) {
    return CourseItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      originalPrice: json['original_price'] != null ? (json['original_price'] as num).toDouble() : null,
      rating: (json['rating'] as num?)?.toDouble() ?? 0,
      reviewsCount: (json['reviews_count'] as num?)?.toInt() ?? 0,
      studentsCount: (json['students_count'] as num?)?.toInt() ?? 0,
      coverImage: json['cover_image']?.toString(),
      category: json['category'] != null ? CategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      instructor: json['instructor'] != null ? InstructorRef.fromJson(json['instructor'] as Map<String, dynamic>) : null,
    );
  }
}

class CategoryRef {
  CategoryRef({required this.id, required this.name});
  final int id;
  final String name;
  factory CategoryRef.fromJson(Map<String, dynamic> json) {
    return CategoryRef(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
    );
  }
}

class InstructorRef {
  InstructorRef({required this.id, required this.name});
  final int id;
  final String name;
  factory InstructorRef.fromJson(Map<String, dynamic> json) {
    return InstructorRef(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
    );
  }
}

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';

class CoursesApi {
  CoursesApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// تفاصيل دورة واحدة بالـ slug
  static Future<CourseDetailItem?> getCourseBySlug(String slug) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCoursesList/$slug');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return CourseDetailItem.fromJson(data);
    } catch (_) {
      return null;
    }
  }

  /// قائمة الدورات حسب التصنيف (categoryId واختياري subCategoryId للفرعي)
  static Future<List<CourseItem>> getCoursesByCategory({required int categoryId, int? subCategoryId}) async {
    try {
      final query = <String, String>{'category': categoryId.toString()};
      if (subCategoryId != null && subCategoryId > 0) query['sub'] = subCategoryId.toString();
      final uri = Uri.parse('$apiBaseUrl$apiCoursesList').replace(queryParameters: query);
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['courses'] as List<dynamic>?) ?? [];
        return list.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  /// جلب تصنيفات الدورات من إدارة الدورات التدريبية (Category)
  static Future<CourseCategoriesResponse> getCategories() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCoursesCategories');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['categories'] as List<dynamic>?) ?? [];
        final categories = list
            .map((e) => CourseCategoryItem.fromJson(e as Map<String, dynamic>))
            .toList();
        return CourseCategoriesResponse(categories: categories);
      }
      return CourseCategoriesResponse(categories: []);
    } catch (_) {
      return CourseCategoriesResponse(categories: []);
    }
  }
}

class CourseCategoriesResponse {
  CourseCategoriesResponse({required this.categories});
  final List<CourseCategoryItem> categories;
}

class CourseCategoryItem {
  CourseCategoryItem({
    required this.id,
    required this.name,
    required this.slug,
    this.image,
    this.icon,
    this.description,
    required this.publishedCoursesCount,
    required this.children,
  });

  final int id;
  final String name;
  final String slug;
  final String? image;
  final String? icon;
  final String? description;
  final int publishedCoursesCount;
  final List<CourseCategoryChild> children;

  factory CourseCategoryItem.fromJson(Map<String, dynamic> json) {
    final childrenRaw = (json['children'] as List<dynamic>?) ?? [];
    final children = childrenRaw
        .map((e) => CourseCategoryChild.fromJson(e as Map<String, dynamic>))
        .toList();
    return CourseCategoryItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      image: json['image']?.toString(),
      icon: json['icon']?.toString(),
      description: json['description']?.toString(),
      publishedCoursesCount: (json['published_courses_count'] as num?)?.toInt() ?? 0,
      children: children,
    );
  }
}

class CourseDetailItem {
  CourseDetailItem({
    required this.id,
    required this.title,
    required this.slug,
    required this.description,
    required this.objectives,
    this.coverImage,
    this.previewVideoUrl,
    required this.price,
    this.originalPrice,
    required this.rating,
    required this.reviewsCount,
    required this.studentsCount,
    required this.hours,
    required this.lessonsCount,
    this.category,
    this.instructor,
  });

  final int id;
  final String title;
  final String slug;
  final String description;
  final String objectives;
  final String? coverImage;
  final String? previewVideoUrl;
  final double price;
  final double? originalPrice;
  final double rating;
  final int reviewsCount;
  final int studentsCount;
  final int hours;
  final int lessonsCount;
  final CategoryRef? category;
  final InstructorDetailRef? instructor;

  bool get hasDiscount => originalPrice != null && originalPrice! > price;

  factory CourseDetailItem.fromJson(Map<String, dynamic> json) {
    return CourseDetailItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      objectives: (json['objectives'] ?? '').toString(),
      coverImage: json['cover_image']?.toString(),
      previewVideoUrl: json['preview_video_url']?.toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      originalPrice: json['original_price'] != null ? (json['original_price'] as num).toDouble() : null,
      rating: (json['rating'] as num?)?.toDouble() ?? 0,
      reviewsCount: (json['reviews_count'] as num?)?.toInt() ?? 0,
      studentsCount: (json['students_count'] as num?)?.toInt() ?? 0,
      hours: (json['hours'] as num?)?.toInt() ?? 0,
      lessonsCount: (json['lessons_count'] as num?)?.toInt() ?? 0,
      category: json['category'] != null ? CategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      instructor: json['instructor'] != null ? InstructorDetailRef.fromJson(json['instructor'] as Map<String, dynamic>) : null,
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

class InstructorDetailRef {
  InstructorDetailRef({required this.id, required this.name, this.avatar});
  final int id;
  final String name;
  final String? avatar;
  factory InstructorDetailRef.fromJson(Map<String, dynamic> json) {
    return InstructorDetailRef(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      avatar: json['avatar']?.toString(),
    );
  }
}

class CourseCategoryChild {
  CourseCategoryChild({
    required this.id,
    required this.name,
    required this.slug,
    this.image,
    this.icon,
    required this.publishedCoursesCount,
  });

  final int id;
  final String name;
  final String slug;
  final String? image;
  final String? icon;
  final int publishedCoursesCount;

  factory CourseCategoryChild.fromJson(Map<String, dynamic> json) {
    return CourseCategoryChild(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      image: json['image']?.toString(),
      icon: json['icon']?.toString(),
      publishedCoursesCount: (json['published_courses_count'] as num?)?.toInt() ?? 0,
    );
  }
}

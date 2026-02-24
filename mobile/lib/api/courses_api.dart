import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class CoursesApi {
  CoursesApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
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

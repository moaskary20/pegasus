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

  /// قائمة الدورات حسب التصنيف مع فلاتر اختيارية
  static Future<List<CourseItem>> getCourses({
    required int categoryId,
    int? subCategoryId,
    double? minRating,
    String? priceType,
    double? minPrice,
    double? maxPrice,
    String? sort,
  }) async {
    try {
      final query = <String, String>{'category': categoryId.toString()};
      if (subCategoryId != null && subCategoryId > 0) query['sub'] = subCategoryId.toString();
      if (minRating != null && minRating > 0) query['min_rating'] = minRating.toString();
      if (priceType != null && priceType.isNotEmpty) query['price_type'] = priceType;
      if (minPrice != null) query['min_price'] = minPrice.toString();
      if (maxPrice != null) query['max_price'] = maxPrice.toString();
      if (sort != null && sort.isNotEmpty) query['sort'] = sort;

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

  /// قائمة الدورات حسب التصنيف (توافق مع الاستدعاءات القديمة)
  static Future<List<CourseItem>> getCoursesByCategory({required int categoryId, int? subCategoryId}) =>
      getCourses(categoryId: categoryId, subCategoryId: subCategoryId);

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
    this.announcement,
    this.level,
    this.levelLabel,
    this.coverImage,
    this.previewVideoUrl,
    required this.price,
    this.originalPrice,
    this.priceOnce,
    this.priceMonthly,
    this.priceDaily,
    required this.rating,
    required this.reviewsCount,
    required this.studentsCount,
    required this.hours,
    required this.lessonsCount,
    this.category,
    this.subCategory,
    this.instructor,
    this.sections = const [],
    this.isEnrolled = false,
    this.progressPercentage = 0,
    this.lessonProgressMap = const {},
    this.relatedCourses = const [],
    this.ratings = const [],
    this.completedAt,
  });

  final int id;
  final String title;
  final String slug;
  final String description;
  final String objectives;
  final String? announcement;
  final String? level;
  final String? levelLabel;
  final String? coverImage;
  final String? previewVideoUrl;
  final double price;
  final double? originalPrice;
  final double? priceOnce;
  final double? priceMonthly;
  final double? priceDaily;
  final double rating;
  final int reviewsCount;
  final int studentsCount;
  final int hours;
  final int lessonsCount;
  final CategoryRef? category;
  final CategoryRef? subCategory;
  final InstructorDetailRef? instructor;
  final List<CourseSectionItem> sections;
  final bool isEnrolled;
  final double progressPercentage;
  final Map<int, LessonProgress> lessonProgressMap;
  final List<CourseItem> relatedCourses;
  final List<CourseRatingItem> ratings;
  final String? completedAt;

  bool get hasDiscount => originalPrice != null && originalPrice! > price;

  factory CourseDetailItem.fromJson(Map<String, dynamic> json) {
    final sectionsRaw = (json['sections'] as List<dynamic>?) ?? [];
    final sections = sectionsRaw
        .map((e) => CourseSectionItem.fromJson(e as Map<String, dynamic>))
        .toList();

    Map<int, LessonProgress> lessonProgressMap = {};
    final lpRaw = json['lesson_progress_map'];
    if (lpRaw is Map) {
      for (final e in lpRaw.entries) {
        final k = int.tryParse(e.key.toString()) ?? 0;
        if (k > 0 && e.value is Map) {
          final v = e.value as Map<String, dynamic>;
          lessonProgressMap[k] = LessonProgress(
            completed: (v['completed'] as bool?) ?? false,
            lastPosition: (v['last_position'] as num?)?.toInt() ?? 0,
          );
        }
      }
    }

    final relatedRaw = (json['related_courses'] as List<dynamic>?) ?? [];
    final relatedCourses = relatedRaw.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList();

    final ratingsRaw = (json['ratings'] as List<dynamic>?) ?? [];
    final ratings = ratingsRaw.map((e) => CourseRatingItem.fromJson(e as Map<String, dynamic>)).toList();

    return CourseDetailItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      objectives: (json['objectives'] ?? '').toString(),
      announcement: json['announcement']?.toString(),
      level: json['level']?.toString(),
      levelLabel: json['level_label']?.toString(),
      coverImage: json['cover_image']?.toString(),
      previewVideoUrl: json['preview_video_url']?.toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      originalPrice: json['original_price'] != null ? (json['original_price'] as num).toDouble() : null,
      priceOnce: json['price_once'] != null ? (json['price_once'] as num).toDouble() : null,
      priceMonthly: json['price_monthly'] != null ? (json['price_monthly'] as num).toDouble() : null,
      priceDaily: json['price_daily'] != null ? (json['price_daily'] as num).toDouble() : null,
      rating: (json['rating'] as num?)?.toDouble() ?? 0,
      reviewsCount: (json['reviews_count'] as num?)?.toInt() ?? 0,
      studentsCount: (json['students_count'] as num?)?.toInt() ?? 0,
      hours: (json['hours'] as num?)?.toInt() ?? 0,
      lessonsCount: (json['lessons_count'] as num?)?.toInt() ?? 0,
      category: json['category'] != null ? CategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      subCategory: json['sub_category'] != null ? CategoryRef.fromJson(json['sub_category'] as Map<String, dynamic>) : null,
      instructor: json['instructor'] != null ? InstructorDetailRef.fromJson(json['instructor'] as Map<String, dynamic>) : null,
      sections: sections,
      isEnrolled: (json['is_enrolled'] as bool?) ?? false,
      progressPercentage: (json['progress_percentage'] as num?)?.toDouble() ?? 0,
      lessonProgressMap: lessonProgressMap,
      relatedCourses: relatedCourses,
      ratings: ratings,
      completedAt: json['completed_at']?.toString(),
    );
  }
}

class LessonProgress {
  LessonProgress({required this.completed, required this.lastPosition});
  final bool completed;
  final int lastPosition;
}

class CourseRatingItem {
  CourseRatingItem({
    required this.userName,
    required this.stars,
    this.review,
    this.createdAt,
    this.avatar,
  });
  final String userName;
  final int stars;
  final String? review;
  final String? createdAt;
  final String? avatar;

  factory CourseRatingItem.fromJson(Map<String, dynamic> json) {
    return CourseRatingItem(
      userName: (json['user_name'] ?? 'مستخدم').toString(),
      stars: (json['stars'] as num?)?.toInt() ?? 0,
      review: json['review']?.toString(),
      createdAt: json['created_at']?.toString(),
      avatar: json['avatar']?.toString(),
    );
  }
}

class CourseSectionItem {
  CourseSectionItem({
    required this.id,
    required this.title,
    required this.sortOrder,
    required this.lessons,
  });

  final int id;
  final String title;
  final int sortOrder;
  final List<CourseLessonItem> lessons;

  factory CourseSectionItem.fromJson(Map<String, dynamic> json) {
    final lessonsRaw = (json['lessons'] as List<dynamic>?) ?? [];
    final lessons = lessonsRaw
        .map((e) => CourseLessonItem.fromJson(e as Map<String, dynamic>))
        .toList();
    return CourseSectionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
      lessons: lessons,
    );
  }
}

class CourseLessonItem {
  CourseLessonItem({
    required this.id,
    required this.title,
    required this.durationMinutes,
    required this.isFreePreview,
    required this.sortOrder,
  });

  final int id;
  final String title;
  final int durationMinutes;
  final bool isFreePreview;
  final int sortOrder;

  String get durationLabel {
    if (durationMinutes < 60) return '$durationMinutes د';
    final h = durationMinutes ~/ 60;
    final m = durationMinutes % 60;
    if (m == 0) return '${h} س';
    return '$h س $m د';
  }

  factory CourseLessonItem.fromJson(Map<String, dynamic> json) {
    return CourseLessonItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      durationMinutes: (json['duration_minutes'] as num?)?.toInt() ?? 0,
      isFreePreview: (json['is_free_preview'] as bool?) ?? false,
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
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

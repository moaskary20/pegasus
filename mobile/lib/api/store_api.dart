import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class StoreApi {
  StoreApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب تصنيفات المتجر من إدارة المتجر (ProductCategory)
  static Future<StoreCategoriesResponse> getCategories() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiStoreCategories');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['categories'] as List<dynamic>?) ?? [];
        final categories = list
            .map((e) => StoreCategory.fromJson(e as Map<String, dynamic>))
            .toList();
        return StoreCategoriesResponse(categories: categories);
      }
      return StoreCategoriesResponse(categories: []);
    } catch (_) {
      return StoreCategoriesResponse(categories: []);
    }
  }
}

class StoreCategoriesResponse {
  StoreCategoriesResponse({required this.categories});
  final List<StoreCategory> categories;
}

class StoreCategory {
  StoreCategory({
    required this.id,
    required this.name,
    required this.slug,
    this.image,
    required this.productsCount,
    required this.children,
  });

  final int id;
  final String name;
  final String slug;
  final String? image;
  final int productsCount;
  final List<StoreCategoryChild> children;

  factory StoreCategory.fromJson(Map<String, dynamic> json) {
    final childrenRaw = (json['children'] as List<dynamic>?) ?? [];
    final children = childrenRaw
        .map((e) => StoreCategoryChild.fromJson(e as Map<String, dynamic>))
        .toList();
    return StoreCategory(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      image: json['image']?.toString(),
      productsCount: (json['products_count'] as num?)?.toInt() ?? 0,
      children: children,
    );
  }
}

class StoreCategoryChild {
  StoreCategoryChild({
    required this.id,
    required this.name,
    required this.slug,
    this.image,
    required this.productsCount,
  });

  final int id;
  final String name;
  final String slug;
  final String? image;
  final int productsCount;

  factory StoreCategoryChild.fromJson(Map<String, dynamic> json) {
    return StoreCategoryChild(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      image: json['image']?.toString(),
      productsCount: (json['products_count'] as num?)?.toInt() ?? 0,
    );
  }
}

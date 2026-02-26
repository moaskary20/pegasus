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

  /// قائمة المنتجات حسب التصنيف
  static Future<List<StoreProductItem>> getProductsByCategory({required int categoryId, int? subCategoryId}) async {
    try {
      final query = <String, String>{'category': categoryId.toString()};
      if (subCategoryId != null && subCategoryId > 0) query['sub'] = subCategoryId.toString();
      final uri = Uri.parse('$apiBaseUrl$apiStoreProducts').replace(queryParameters: query);
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['products'] as List<dynamic>?) ?? [];
        return list.map((e) => StoreProductItem.fromJson(e as Map<String, dynamic>)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  /// تفاصيل منتج بالـ slug
  static Future<StoreProductDetail?> getProductBySlug(String slug) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiStoreProductDetail/$slug');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return StoreProductDetail.fromJson(data);
    } catch (_) {
      return null;
    }
  }

  /// تقييم منتج (1-5 نجوم، تعليق اختياري)
  static Future<ProductRateResult?> rateProduct(int productId, {required int rating, String? comment}) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) return null;
      final uri = Uri.parse('$apiBaseUrl$apiStoreProductRate/$productId/rate');
      final body = <String, dynamic>{'rating': rating};
      if (comment != null && comment.trim().isNotEmpty) body['comment'] = comment.trim();
      final res = await http.post(
        uri,
        headers: {..._headers, 'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );
      if (res.statusCode != 200) return null;
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return ProductRateResult(
        success: data['success'] == true,
        message: data['message']?.toString(),
        averageRating: (data['average_rating'] as num?)?.toDouble(),
        ratingsCount: (data['ratings_count'] as num?)?.toInt(),
      );
    } catch (_) {
      return null;
    }
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

class ProductRateResult {
  ProductRateResult({
    required this.success,
    this.message,
    this.averageRating,
    this.ratingsCount,
  });
  final bool success;
  final String? message;
  final double? averageRating;
  final int? ratingsCount;
}

class StoreProductItem {
  StoreProductItem({
    required this.id,
    required this.name,
    required this.slug,
    required this.price,
    this.comparePrice,
    this.mainImage,
    this.category,
    required this.averageRating,
    required this.ratingsCount,
    required this.salesCount,
  });

  final int id;
  final String name;
  final String slug;
  final double price;
  final double? comparePrice;
  final String? mainImage;
  final StoreCategoryRef? category;
  final double averageRating;
  final int ratingsCount;
  final int salesCount;

  bool get hasDiscount => comparePrice != null && comparePrice! > price;

  factory StoreProductItem.fromJson(Map<String, dynamic> json) {
    return StoreProductItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      comparePrice: json['compare_price'] != null ? (json['compare_price'] as num).toDouble() : null,
      mainImage: json['main_image']?.toString(),
      category: json['category'] != null ? StoreCategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      averageRating: (json['average_rating'] as num?)?.toDouble() ?? 0,
      ratingsCount: (json['ratings_count'] as num?)?.toInt() ?? 0,
      salesCount: (json['sales_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class StoreCategoryRef {
  StoreCategoryRef({required this.id, required this.name});
  final int id;
  final String name;
  factory StoreCategoryRef.fromJson(Map<String, dynamic> json) {
    return StoreCategoryRef(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
    );
  }
}

class StoreProductDetail {
  StoreProductDetail({
    required this.id,
    required this.name,
    required this.slug,
    this.sku,
    required this.shortDescription,
    required this.description,
    required this.price,
    this.comparePrice,
    this.discountPercentage,
    this.mainImage,
    required this.images,
    this.category,
    required this.averageRating,
    required this.ratingsCount,
    required this.salesCount,
    this.quantity,
    required this.trackQuantity,
    required this.stockStatus,
    required this.stockStatusLabel,
    this.weight,
    this.dimensions,
    required this.isDigital,
    required this.requiresShipping,
  });

  final int id;
  final String name;
  final String slug;
  final String? sku;
  final String shortDescription;
  final String description;
  final double price;
  final double? comparePrice;
  final double? discountPercentage;
  final String? mainImage;
  final List<String> images;
  final StoreCategoryRef? category;
  final double averageRating;
  final int ratingsCount;
  final int salesCount;
  final int? quantity;
  final bool trackQuantity;
  final String stockStatus;
  final String stockStatusLabel;
  final double? weight;
  final String? dimensions;
  final bool isDigital;
  final bool requiresShipping;

  bool get hasDiscount => comparePrice != null && comparePrice! > price;
  bool get isInStock => stockStatus != 'out_of_stock';

  factory StoreProductDetail.fromJson(Map<String, dynamic> json) {
    final imagesRaw = (json['images'] as List<dynamic>?) ?? [];
    final images = imagesRaw.map((e) => e.toString()).toList();
    return StoreProductDetail(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      sku: json['sku']?.toString(),
      shortDescription: (json['short_description'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      comparePrice: json['compare_price'] != null ? (json['compare_price'] as num).toDouble() : null,
      discountPercentage: json['discount_percentage'] != null ? (json['discount_percentage'] as num).toDouble() : null,
      mainImage: json['main_image']?.toString(),
      images: images,
      category: json['category'] != null ? StoreCategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      averageRating: (json['average_rating'] as num?)?.toDouble() ?? 0,
      ratingsCount: (json['ratings_count'] as num?)?.toInt() ?? 0,
      salesCount: (json['sales_count'] as num?)?.toInt() ?? 0,
      quantity: json['quantity'] != null ? (json['quantity'] as num).toInt() : null,
      trackQuantity: (json['track_quantity'] as bool?) ?? false,
      stockStatus: (json['stock_status'] ?? 'available').toString(),
      stockStatusLabel: (json['stock_status_label'] ?? 'متوفر').toString(),
      weight: json['weight'] != null ? (json['weight'] as num).toDouble() : null,
      dimensions: json['dimensions']?.toString(),
      isDigital: (json['is_digital'] as bool?) ?? false,
      requiresShipping: (json['requires_shipping'] as bool?) ?? true,
    );
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

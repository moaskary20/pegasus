import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';
import 'store_api.dart';

class WishlistApi {
  WishlistApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<WishlistResponse> getWishlist() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiWishlist');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return WishlistResponse(courses: [], products: [], courseIds: [], productIds: [], needsAuth: true);
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final coursesRaw = (data['courses'] as List<dynamic>?) ?? [];
        final productsRaw = (data['products'] as List<dynamic>?) ?? [];
        final courseIds = (data['wishlist_course_ids'] as List<dynamic>?)?.cast<int>() ?? [];
        final productIds = (data['wishlist_product_ids'] as List<dynamic>?)?.cast<int>() ?? [];
        final courses = coursesRaw.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList();
        final products = productsRaw.map((e) => StoreProductItem.fromJson(e as Map<String, dynamic>)).toList();
        return WishlistResponse(courses: courses, products: products, courseIds: courseIds, productIds: productIds, needsAuth: false);
      }
      return WishlistResponse(courses: [], products: [], courseIds: [], productIds: [], needsAuth: false);
    } catch (_) {
      return WishlistResponse(courses: [], products: [], courseIds: [], productIds: [], needsAuth: false);
    }
  }

  static Future<bool> addCourse(int courseId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiWishlist/courses/$courseId');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> removeCourse(int courseId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiWishlist/courses/$courseId');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> addProduct(int productId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiWishlist/products/$productId');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> removeProduct(int productId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiWishlist/products/$productId');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class WishlistResponse {
  WishlistResponse({
    required this.courses,
    required this.products,
    required this.courseIds,
    required this.productIds,
    this.needsAuth = false,
  });
  final List<CourseItem> courses;
  final List<StoreProductItem> products;
  final List<int> courseIds;
  final List<int> productIds;
  final bool needsAuth;
}

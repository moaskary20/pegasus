import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';
import 'store_api.dart';

class CartApi {
  CartApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<CartResponse> getCart() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCart');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return CartResponse(
          courses: [],
          cartProducts: [],
          coursesSubtotal: 0,
          productsSubtotal: 0,
          total: 0,
          needsAuth: true,
        );
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final coursesRaw = (data['courses'] as List<dynamic>?) ?? [];
        final productsRaw = (data['cart_products'] as List<dynamic>?) ?? [];
        final courses = coursesRaw.map((e) => CartCourseItem.fromJson(e as Map<String, dynamic>)).toList();
        final cartProducts = productsRaw.map((e) => CartProductItem.fromJson(e as Map<String, dynamic>)).toList();
        return CartResponse(
          courses: courses,
          cartProducts: cartProducts,
          coursesSubtotal: (data['courses_subtotal'] as num?)?.toDouble() ?? 0,
          productsSubtotal: (data['products_subtotal'] as num?)?.toDouble() ?? 0,
          total: (data['total'] as num?)?.toDouble() ?? 0,
          needsAuth: false,
        );
      }
      return CartResponse(courses: [], cartProducts: [], coursesSubtotal: 0, productsSubtotal: 0, total: 0, needsAuth: false);
    } catch (_) {
      return CartResponse(courses: [], cartProducts: [], coursesSubtotal: 0, productsSubtotal: 0, total: 0, needsAuth: false);
    }
  }

  static Future<bool> addCourse(int courseId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCart/courses/$courseId');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> removeCourse(int courseId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCart/courses/$courseId');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> addProduct(int productId, {int quantity = 1}) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCart/products/$productId');
      final headers = {..._headers, 'Content-Type': 'application/json'};
      final res = await http.post(uri, headers: headers, body: jsonEncode({'quantity': quantity}));
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// إزالة عنصر من سلة المتجر (id = عنصر السلة StoreCart id)
  static Future<bool> removeProduct(int cartItemId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiCart/products/$cartItemId');
      final res = await http.delete(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class CartResponse {
  CartResponse({
    required this.courses,
    required this.cartProducts,
    required this.coursesSubtotal,
    required this.productsSubtotal,
    required this.total,
    this.needsAuth = false,
  });
  final List<CartCourseItem> courses;
  final List<CartProductItem> cartProducts;
  final double coursesSubtotal;
  final double productsSubtotal;
  final double total;
  final bool needsAuth;
}

class CartCourseItem {
  CartCourseItem({
    required this.id,
    required this.title,
    required this.slug,
    required this.price,
    this.originalPrice,
    this.coverImage,
    this.category,
    this.instructor,
  });

  final int id;
  final String title;
  final String slug;
  final double price;
  final double? originalPrice;
  final String? coverImage;
  final CategoryRef? category;
  final InstructorRef? instructor;

  factory CartCourseItem.fromJson(Map<String, dynamic> json) {
    return CartCourseItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      originalPrice: json['original_price'] != null ? (json['original_price'] as num).toDouble() : null,
      coverImage: json['cover_image']?.toString(),
      category: json['category'] != null ? CategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
      instructor: json['instructor'] != null ? InstructorRef.fromJson(json['instructor'] as Map<String, dynamic>) : null,
    );
  }
}

class CartProductItem {
  CartProductItem({
    required this.id,
    required this.productId,
    required this.name,
    required this.slug,
    this.mainImage,
    required this.unitPrice,
    required this.quantity,
    required this.total,
    this.category,
  });

  final int id;
  final int productId;
  final String name;
  final String slug;
  final String? mainImage;
  final double unitPrice;
  final int quantity;
  final double total;
  final StoreCategoryRef? category;

  factory CartProductItem.fromJson(Map<String, dynamic> json) {
    return CartProductItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      productId: (json['product_id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      mainImage: json['main_image']?.toString(),
      unitPrice: (json['unit_price'] as num?)?.toDouble() ?? 0,
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
      total: (json['total'] as num?)?.toDouble() ?? 0,
      category: json['category'] != null ? StoreCategoryRef.fromJson(json['category'] as Map<String, dynamic>) : null,
    );
  }
}

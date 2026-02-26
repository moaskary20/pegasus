import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class CheckoutApi {
  CheckoutApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<CheckoutPreviewResponse> getPreview() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiCheckoutPreview');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return CheckoutPreviewResponse(needsAuth: true);
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final coursesRaw = (data['courses'] as List<dynamic>?) ?? [];
        final productsRaw = (data['cart_products'] as List<dynamic>?) ?? [];
        return CheckoutPreviewResponse(
          courses: coursesRaw.map((e) => CheckoutCourseItem.fromJson(e as Map<String, dynamic>)).toList(),
          cartProducts: productsRaw.map((e) => CheckoutProductItem.fromJson(e as Map<String, dynamic>)).toList(),
          coursesSubtotal: (data['courses_subtotal'] as num?)?.toDouble() ?? 0,
          productsSubtotal: (data['products_subtotal'] as num?)?.toDouble() ?? 0,
          total: (data['total'] as num?)?.toDouble() ?? 0,
          paymentMethods: (data['payment_methods'] as List<dynamic>?)
                  ?.map((e) => PaymentMethod.fromJson(e as Map<String, dynamic>))
                  .toList() ??
              _defaultPaymentMethods(),
          needsAuth: false,
        );
      }
      return CheckoutPreviewResponse(needsAuth: false);
    } catch (_) {
      return CheckoutPreviewResponse(needsAuth: false);
    }
  }

  static List<PaymentMethod> get defaultPaymentMethods => [
    PaymentMethod(id: 'kashier', label: 'الدفع بالفيزا والبطاقات البنكية', description: 'VISA • MasterCard • ميزة'),
    PaymentMethod(id: 'manual', label: 'تحويل/دفع يدوي', description: 'إرفاق إيصال التحويل'),
  ];

  static List<PaymentMethod> _defaultPaymentMethods() => defaultPaymentMethods;

  /// التحقق من الكوبون ومعاينة الخصم
  static Future<CouponValidationResult> validateCoupon(String couponCode) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) {
        return CouponValidationResult(valid: false, message: 'يجب تسجيل الدخول', discount: 0, total: 0);
      }
      if (couponCode.trim().isEmpty) {
        return CouponValidationResult(valid: false, message: 'أدخل رمز الكوبون', discount: 0, total: 0);
      }
      final uri = Uri.parse('$apiBaseUrl$apiCheckoutValidateCoupon');
      final res = await http.post(
        uri,
        headers: {..._headers, 'Content-Type': 'application/json'},
        body: jsonEncode({'coupon_code': couponCode.trim()}),
      );
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      return CouponValidationResult(
        valid: data['valid'] == true,
        message: data['message']?.toString() ?? '',
        couponCode: data['coupon_code']?.toString(),
        discount: (data['discount'] as num?)?.toDouble() ?? 0,
        total: (data['total'] as num?)?.toDouble() ?? 0,
        coursesSubtotal: (data['courses_subtotal'] as num?)?.toDouble(),
        productsSubtotal: (data['products_subtotal'] as num?)?.toDouble(),
      );
    } catch (_) {
      return CouponValidationResult(valid: false, message: 'تحقق من الاتصال', discount: 0, total: 0);
    }
  }

  static Future<CheckoutProcessResult> process({
    required String paymentGateway,
    String? couponCode,
    File? manualReceiptFile,
  }) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) {
        return CheckoutProcessResult(success: false, message: 'يجب تسجيل الدخول');
      }

      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiBaseUrl$apiCheckoutProcess'),
      );
      request.headers['Accept'] = 'application/json';
      request.headers['Authorization'] = 'Bearer ${AuthApi.token}';
      request.fields['payment_gateway'] = paymentGateway;
      if (couponCode != null && couponCode.trim().isNotEmpty) {
        request.fields['coupon_code'] = couponCode.trim();
      }
      if (manualReceiptFile != null) {
        request.files.add(await http.MultipartFile.fromPath(
          'manual_receipt',
          manualReceiptFile.path,
        ));
      }

      final streamed = await request.send();
      final res = await http.Response.fromStream(streamed);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};

      if (res.statusCode == 200) {
        return CheckoutProcessResult(
          success: true,
          message: data['message'] as String? ?? 'تم إنشاء الطلب بنجاح',
          orderId: (data['order_id'] as num?)?.toInt(),
          storeOrderId: (data['store_order_id'] as num?)?.toInt(),
        );
      }
      final message = data['message'] as String? ?? 'حدث خطأ';
      final errors = data['errors'] as Map<String, dynamic>?;
      String? errMsg = message;
      if (errors != null && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) {
          errMsg = first.first.toString();
        } else if (first is String) {
          errMsg = first;
        }
      }
      return CheckoutProcessResult(success: false, message: errMsg ?? 'حدث خطأ');
    } catch (e) {
      return CheckoutProcessResult(success: false, message: 'تحقق من الاتصال وحاول مرة أخرى');
    }
  }
}

class CheckoutPreviewResponse {
  CheckoutPreviewResponse({
    this.courses = const [],
    this.cartProducts = const [],
    this.coursesSubtotal = 0,
    this.productsSubtotal = 0,
    this.total = 0,
    this.paymentMethods = const [],
    this.needsAuth = false,
  });

  final List<CheckoutCourseItem> courses;
  final List<CheckoutProductItem> cartProducts;
  final double coursesSubtotal;
  final double productsSubtotal;
  final double total;
  final List<PaymentMethod> paymentMethods;
  final bool needsAuth;
}

class CheckoutCourseItem {
  CheckoutCourseItem({required this.id, required this.title, required this.slug, required this.price, this.coverImage});

  final int id;
  final String title;
  final String slug;
  final double price;
  final String? coverImage;

  factory CheckoutCourseItem.fromJson(Map<String, dynamic> json) {
    return CheckoutCourseItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      coverImage: json['cover_image']?.toString(),
    );
  }
}

class CheckoutProductItem {
  CheckoutProductItem({
    required this.id,
    required this.productId,
    required this.name,
    required this.quantity,
    required this.unitPrice,
    required this.total,
  });

  final int id;
  final int productId;
  final String name;
  final int quantity;
  final double unitPrice;
  final double total;

  factory CheckoutProductItem.fromJson(Map<String, dynamic> json) {
    return CheckoutProductItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      productId: (json['product_id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
      unitPrice: (json['unit_price'] as num?)?.toDouble() ?? 0,
      total: (json['total'] as num?)?.toDouble() ?? 0,
    );
  }
}

class PaymentMethod {
  PaymentMethod({required this.id, required this.label, this.description});

  final String id;
  final String label;
  final String? description;

  factory PaymentMethod.fromJson(Map<String, dynamic> json) {
    return PaymentMethod(
      id: (json['id'] ?? '').toString(),
      label: (json['label'] ?? '').toString(),
      description: json['description']?.toString(),
    );
  }
}

class CouponValidationResult {
  CouponValidationResult({
    required this.valid,
    required this.message,
    this.couponCode,
    required this.discount,
    required this.total,
    this.coursesSubtotal,
    this.productsSubtotal,
  });
  final bool valid;
  final String message;
  final String? couponCode;
  final double discount;
  final double total;
  final double? coursesSubtotal;
  final double? productsSubtotal;
}

class CheckoutProcessResult {
  CheckoutProcessResult({
    required this.success,
    this.message,
    this.orderId,
    this.storeOrderId,
  });

  final bool success;
  final String? message;
  final int? orderId;
  final int? storeOrderId;
}

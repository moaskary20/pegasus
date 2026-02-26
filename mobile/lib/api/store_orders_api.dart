import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class StoreOrdersApi {
  StoreOrdersApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<StoreOrdersResponse> getStoreOrders() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiStoreOrders');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['orders'] as List<dynamic>?) ?? [];
        return StoreOrdersResponse(
          orders: list.map((e) => StoreOrderItem.fromJson(e as Map<String, dynamic>)).toList(),
          needsAuth: false,
        );
      }
      return StoreOrdersResponse(orders: [], needsAuth: res.statusCode == 401);
    } catch (_) {
      return StoreOrdersResponse(orders: [], needsAuth: false);
    }
  }
}

class StoreOrderItem {
  StoreOrderItem({
    required this.id,
    required this.orderNumber,
    required this.subtotal,
    required this.shippingCost,
    required this.discountAmount,
    required this.total,
    required this.status,
    required this.paymentStatus,
    this.paidAt,
    this.createdAt,
    required this.itemsCount,
    required this.itemsSummary,
  });

  final int id;
  final String orderNumber;
  final double subtotal;
  final double shippingCost;
  final double discountAmount;
  final double total;
  final String status;
  final String paymentStatus;
  final String? paidAt;
  final String? createdAt;
  final int itemsCount;
  final List<StoreOrderItemSummary> itemsSummary;

  factory StoreOrderItem.fromJson(Map<String, dynamic> json) {
    final summary = (json['items_summary'] as List<dynamic>?) ?? [];
    return StoreOrderItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      orderNumber: (json['order_number'] ?? '').toString(),
      subtotal: (json['subtotal'] as num?)?.toDouble() ?? 0,
      shippingCost: (json['shipping_cost'] as num?)?.toDouble() ?? 0,
      discountAmount: (json['discount_amount'] as num?)?.toDouble() ?? 0,
      total: (json['total'] as num?)?.toDouble() ?? 0,
      status: (json['status'] ?? 'pending').toString(),
      paymentStatus: (json['payment_status'] ?? 'pending').toString(),
      paidAt: json['paid_at']?.toString(),
      createdAt: json['created_at']?.toString(),
      itemsCount: (json['items_count'] as num?)?.toInt() ?? 0,
      itemsSummary: summary.map((e) => StoreOrderItemSummary.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class StoreOrderItemSummary {
  StoreOrderItemSummary({
    required this.id,
    required this.productId,
    required this.productName,
    required this.quantity,
    required this.price,
    required this.total,
  });

  final int id;
  final int productId;
  final String productName;
  final int quantity;
  final double price;
  final double total;

  factory StoreOrderItemSummary.fromJson(Map<String, dynamic> json) {
    return StoreOrderItemSummary(
      id: (json['id'] as num?)?.toInt() ?? 0,
      productId: (json['product_id'] as num?)?.toInt() ?? 0,
      productName: (json['product_name'] ?? '—').toString(),
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
      price: (json['price'] as num?)?.toDouble() ?? 0,
      total: (json['total'] as num?)?.toDouble() ?? 0,
    );
  }
}

class StoreOrdersResponse {
  StoreOrdersResponse({required this.orders, this.needsAuth = false});
  final List<StoreOrderItem> orders;
  final bool needsAuth;
}

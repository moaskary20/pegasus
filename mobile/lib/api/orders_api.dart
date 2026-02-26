import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class OrdersApi {
  OrdersApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<OrdersResponse> getOrders() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiOrders');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['orders'] as List<dynamic>?) ?? [];
        return OrdersResponse(
          orders: list.map((e) => OrderItem.fromJson(e as Map<String, dynamic>)).toList(),
          needsAuth: false,
        );
      }
      return OrdersResponse(
        orders: [],
        needsAuth: res.statusCode == 401,
      );
    } catch (_) {
      return OrdersResponse(orders: [], needsAuth: false);
    }
  }
}

class OrderItem {
  OrderItem({
    required this.id,
    required this.orderNumber,
    required this.subtotal,
    required this.discount,
    required this.total,
    required this.status,
    this.paidAt,
    this.createdAt,
    required this.itemsCount,
    required this.itemsSummary,
  });

  final int id;
  final String orderNumber;
  final double subtotal;
  final double discount;
  final double total;
  final String status;
  final String? paidAt;
  final String? createdAt;
  final int itemsCount;
  final List<OrderItemSummary> itemsSummary;

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    final summary = (json['items_summary'] as List<dynamic>?) ?? [];
    return OrderItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      orderNumber: (json['order_number'] ?? '').toString(),
      subtotal: (json['subtotal'] as num?)?.toDouble() ?? 0,
      discount: (json['discount'] as num?)?.toDouble() ?? 0,
      total: (json['total'] as num?)?.toDouble() ?? 0,
      status: (json['status'] ?? 'pending').toString(),
      paidAt: json['paid_at']?.toString(),
      createdAt: json['created_at']?.toString(),
      itemsCount: (json['items_count'] as num?)?.toInt() ?? 0,
      itemsSummary: summary.map((e) => OrderItemSummary.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class OrderItemSummary {
  OrderItemSummary({
    required this.title,
    required this.quantity,
    required this.price,
  });

  final String title;
  final int quantity;
  final double price;

  factory OrderItemSummary.fromJson(Map<String, dynamic> json) {
    return OrderItemSummary(
      title: (json['title'] ?? '—').toString(),
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
      price: (json['price'] as num?)?.toDouble() ?? 0,
    );
  }
}

class OrdersResponse {
  OrdersResponse({required this.orders, this.needsAuth = false});
  final List<OrderItem> orders;
  final bool needsAuth;
}

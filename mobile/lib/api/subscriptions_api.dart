import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class SubscriptionsApi {
  SubscriptionsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// الخطط المتاحة (بدون مصادقة)
  static Future<SubscriptionsPlansResponse> getPlans() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSubscriptionsPlans');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final plansRaw = (data['plans'] as List<dynamic>?) ?? [];
        return SubscriptionsPlansResponse(
          plans: plansRaw
              .map((e) => SubscriptionPlanItem.fromJson(e as Map<String, dynamic>))
              .toList(),
        );
      }
      return SubscriptionsPlansResponse(plans: []);
    } catch (_) {
      return SubscriptionsPlansResponse(plans: []);
    }
  }

  /// اشتراكات المستخدم (يتطلب مصادقة)
  static Future<MySubscriptionsResponse> getMySubscriptions() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiSubscriptionsMy');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 401) {
        return MySubscriptionsResponse(needsAuth: true, subscriptions: []);
      }
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final subsRaw = (data['subscriptions'] as List<dynamic>?) ?? [];
        return MySubscriptionsResponse(
          subscriptions: subsRaw
              .map((e) => MySubscriptionItem.fromJson(e as Map<String, dynamic>))
              .toList(),
          needsAuth: false,
        );
      }
      return MySubscriptionsResponse(subscriptions: [], needsAuth: false);
    } catch (_) {
      return MySubscriptionsResponse(subscriptions: [], needsAuth: false);
    }
  }

  /// الاشتراك في خطة
  static Future<SubscribeResult> subscribe({
    required int planId,
    required String paymentGateway,
    String? voucherCode,
    File? manualReceiptFile,
  }) async {
    try {
      await AuthApi.loadStoredToken();
      if (AuthApi.token == null) {
        return SubscribeResult(success: false, message: 'يجب تسجيل الدخول');
      }

      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiBaseUrl$apiSubscriptionsSubscribe'),
      );
      request.headers['Accept'] = 'application/json';
      request.headers['Authorization'] = 'Bearer ${AuthApi.token}';
      request.fields['plan_id'] = planId.toString();
      request.fields['payment_gateway'] = paymentGateway;
      if (voucherCode != null && voucherCode.trim().isNotEmpty) {
        request.fields['voucher_code'] = voucherCode.trim();
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
        return SubscribeResult(
          success: true,
          message: data['message'] as String? ?? 'تم الاشتراك بنجاح',
          subscriptionId: (data['subscription_id'] as num?)?.toInt(),
          endDate: data['end_date']?.toString(),
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
      return SubscribeResult(success: false, message: errMsg);
    } catch (e) {
      return SubscribeResult(success: false, message: 'تحقق من الاتصال وحاول مرة أخرى');
    }
  }
}

class SubscriptionsPlansResponse {
  SubscriptionsPlansResponse({this.plans = const []});
  final List<SubscriptionPlanItem> plans;
}

class SubscriptionPlanItem {
  SubscriptionPlanItem({
    required this.id,
    required this.name,
    this.description,
    required this.type,
    this.typeLabel,
    required this.price,
    required this.durationDays,
    this.maxLessons,
  });

  final int id;
  final String name;
  final String? description;
  final String type;
  final String? typeLabel;
  final double price;
  final int durationDays;
  final int? maxLessons;

  factory SubscriptionPlanItem.fromJson(Map<String, dynamic> json) {
    return SubscriptionPlanItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      description: json['description']?.toString(),
      type: (json['type'] ?? 'once').toString(),
      typeLabel: json['type_label']?.toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      durationDays: (json['duration_days'] as num?)?.toInt() ?? 0,
      maxLessons: (json['max_lessons'] as num?)?.toInt(),
    );
  }

  String get typeLabelAr {
    if (typeLabel != null && typeLabel!.isNotEmpty) return typeLabel!;
    switch (type) {
      case 'monthly':
        return 'شهري';
      case 'daily':
        return 'يومي';
      default:
        return 'مرة واحدة';
    }
  }
}

class MySubscriptionsResponse {
  MySubscriptionsResponse({this.subscriptions = const [], this.needsAuth = false});
  final List<MySubscriptionItem> subscriptions;
  final bool needsAuth;
}

class MySubscriptionItem {
  MySubscriptionItem({
    required this.id,
    required this.planId,
    required this.planName,
    this.planType,
    this.planTypeLabel,
    this.startDate,
    this.endDate,
    required this.status,
    required this.isActive,
    this.finalPrice = 0,
  });

  final int id;
  final int planId;
  final String planName;
  final String? planType;
  final String? planTypeLabel;
  final String? startDate;
  final String? endDate;
  final String status;
  final bool isActive;
  final double finalPrice;

  factory MySubscriptionItem.fromJson(Map<String, dynamic> json) {
    return MySubscriptionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      planId: (json['plan_id'] as num?)?.toInt() ?? 0,
      planName: (json['plan_name'] ?? '').toString(),
      planType: json['plan_type']?.toString(),
      planTypeLabel: json['plan_type_label']?.toString(),
      startDate: json['start_date']?.toString(),
      endDate: json['end_date']?.toString(),
      status: (json['status'] ?? 'active').toString(),
      isActive: (json['is_active'] as bool?) ?? false,
      finalPrice: (json['final_price'] as num?)?.toDouble() ?? 0,
    );
  }
}

class SubscribeResult {
  SubscribeResult({
    required this.success,
    this.message,
    this.subscriptionId,
    this.endDate,
  });

  final bool success;
  final String? message;
  final int? subscriptionId;
  final String? endDate;
}

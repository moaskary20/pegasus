import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class InstructorFinancesApi {
  InstructorFinancesApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب بيانات الإدارة المالية للمدرس
  /// يرجع null عند الفشل، مع [errorMessage] اختياري من الـ API
  static Future<InstructorFinancesResponse?> getFinances() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiInstructorFinances');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return InstructorFinancesResponse.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// نفس getFinances لكن يرجع رسالة الخطأ من الـ API عند الفشل
  static Future<({InstructorFinancesResponse? data, String? errorMessage})> getFinancesWithError() async {
    try {
      await AuthApi.loadStoredToken();
      final uri = Uri.parse('$apiBaseUrl$apiInstructorFinances');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return (data: InstructorFinancesResponse.fromJson(data), errorMessage: null);
      }
      final msg = (data['message'] ?? 'تعذر تحميل البيانات').toString();
      return (data: null, errorMessage: msg);
    } catch (_) {
      return (data: null, errorMessage: 'خطأ في الاتصال');
    }
  }
}

class InstructorFinancesResponse {
  InstructorFinancesResponse({
    required this.stats,
    required this.courses,
  });
  final FinancesStats stats;
  final List<CourseEarningItem> courses;

  factory InstructorFinancesResponse.fromJson(Map<String, dynamic> json) {
    final statsRaw = json['stats'] as Map<String, dynamic>? ?? {};
    final coursesRaw = (json['courses'] as List<dynamic>?) ?? [];
    return InstructorFinancesResponse(
      stats: FinancesStats.fromJson(statsRaw),
      courses: coursesRaw.map((e) => CourseEarningItem.fromJson(e as Map<String, dynamic>)).toList(),
    );
  }
}

class FinancesStats {
  FinancesStats({
    required this.totalEarnings,
    required this.availableBalance,
    required this.pendingPayout,
    required this.paidOut,
    required this.minimumPayout,
    required this.commissionRate,
  });
  final double totalEarnings;
  final double availableBalance;
  final double pendingPayout;
  final double paidOut;
  final double minimumPayout;
  final double commissionRate;

  factory FinancesStats.fromJson(Map<String, dynamic> json) {
    return FinancesStats(
      totalEarnings: (json['total_earnings'] as num?)?.toDouble() ?? 0,
      availableBalance: (json['available_balance'] as num?)?.toDouble() ?? 0,
      pendingPayout: (json['pending_payout'] as num?)?.toDouble() ?? 0,
      paidOut: (json['paid_out'] as num?)?.toDouble() ?? 0,
      minimumPayout: (json['minimum_payout'] as num?)?.toDouble() ?? 0,
      commissionRate: (json['commission_rate'] as num?)?.toDouble() ?? 0,
    );
  }
}

class CourseEarningItem {
  CourseEarningItem({
    required this.id,
    required this.title,
    required this.students,
    required this.totalSales,
    required this.commissionRate,
    required this.commissionAmount,
  });
  final int id;
  final String title;
  final int students;
  final double totalSales;
  final double commissionRate;
  final double commissionAmount;

  factory CourseEarningItem.fromJson(Map<String, dynamic> json) {
    return CourseEarningItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      students: (json['students'] as num?)?.toInt() ?? 0,
      totalSales: (json['total_sales'] as num?)?.toDouble() ?? 0,
      commissionRate: (json['commission_rate'] as num?)?.toDouble() ?? 0,
      commissionAmount: (json['commission_amount'] as num?)?.toDouble() ?? 0,
    );
  }
}

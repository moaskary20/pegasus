import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class PointsApi {
  PointsApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// ملخص النقاط (الرصيد، الرتبة، إلخ)
  static Future<PointsSummary?> getSummary() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiPoints');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return PointsSummary.fromJson(data);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// سجل المعاملات (صفحات)
  static Future<PointsTransactionsResponse> getTransactions({int page = 1}) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiPointsTransactions').replace(queryParameters: {'page': page.toString()});
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['data'] as List<dynamic>?) ?? [];
        return PointsTransactionsResponse(
          transactions: list.map((e) => PointTransactionItem.fromJson(e as Map<String, dynamic>)).toList(),
          currentPage: (data['current_page'] as num?)?.toInt() ?? 1,
          lastPage: (data['last_page'] as num?)?.toInt() ?? 1,
          total: (data['total'] as num?)?.toInt() ?? 0,
          needsAuth: false,
        );
      }
      return PointsTransactionsResponse(
        transactions: [],
        currentPage: 1,
        lastPage: 1,
        total: 0,
        needsAuth: res.statusCode == 401,
      );
    } catch (_) {
      return PointsTransactionsResponse(transactions: [], currentPage: 1, lastPage: 1, total: 0, needsAuth: false);
    }
  }

  /// المكافآت المتاحة
  static Future<RewardsResponse> getRewards() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiRewards');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['data'] as List<dynamic>?) ?? [];
        return RewardsResponse(
          rewards: list.map((e) => RewardItem.fromJson(e as Map<String, dynamic>)).toList(),
          needsAuth: false,
        );
      }
      return RewardsResponse(rewards: [], needsAuth: res.statusCode == 401);
    } catch (_) {
      return RewardsResponse(rewards: [], needsAuth: false);
    }
  }

  /// استبدال مكافأة
  static Future<RedeemResult> redeemReward(int rewardId) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiRewardsRedeem/$rewardId/redeem');
      final res = await http.post(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return RedeemResult(
          success: true,
          message: (data['message'] ?? 'تم الاستبدال بنجاح').toString(),
          code: data['redemption']?['code']?.toString(),
          newAvailablePoints: (data['new_available_points'] as num?)?.toInt(),
        );
      }
      return RedeemResult(
        success: false,
        message: (data['message'] ?? 'تعذر الاستبدال').toString(),
      );
    } catch (_) {
      return RedeemResult(success: false, message: 'حدث خطأ في الاتصال');
    }
  }
}

class PointsSummary {
  PointsSummary({
    required this.totalPoints,
    required this.availablePoints,
    required this.rank,
    required this.rankLabel,
    this.rankColor,
    this.pointsForNextRank,
    this.rankPosition,
  });

  final int totalPoints;
  final int availablePoints;
  final String rank;
  final String rankLabel;
  final String? rankColor;
  final int? pointsForNextRank;
  final int? rankPosition;

  factory PointsSummary.fromJson(Map<String, dynamic> json) {
    return PointsSummary(
      totalPoints: (json['total_points'] as num?)?.toInt() ?? 0,
      availablePoints: (json['available_points'] as num?)?.toInt() ?? 0,
      rank: (json['rank'] ?? 'bronze').toString(),
      rankLabel: (json['rank_label'] ?? 'برونزي').toString(),
      rankColor: json['rank_color']?.toString(),
      pointsForNextRank: (json['points_for_next_rank'] as num?)?.toInt(),
      rankPosition: (json['rank_position'] as num?)?.toInt(),
    );
  }
}

class PointTransactionItem {
  PointTransactionItem({
    required this.id,
    required this.points,
    required this.type,
    required this.typeLabel,
    required this.description,
    this.createdAt,
  });

  final int id;
  final int points;
  final String type;
  final String typeLabel;
  final String description;
  final String? createdAt;

  factory PointTransactionItem.fromJson(Map<String, dynamic> json) {
    return PointTransactionItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      points: (json['points'] as num?)?.toInt() ?? 0,
      type: (json['type'] ?? '').toString(),
      typeLabel: (json['type_label'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}

class PointsTransactionsResponse {
  PointsTransactionsResponse({
    required this.transactions,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    this.needsAuth = false,
  });

  final List<PointTransactionItem> transactions;
  final int currentPage;
  final int lastPage;
  final int total;
  final bool needsAuth;
}

class RewardItem {
  RewardItem({
    required this.id,
    required this.name,
    this.description,
    required this.type,
    required this.typeLabel,
    required this.pointsRequired,
    this.value,
    this.image,
    this.course,
    required this.canRedeem,
  });

  final int id;
  final String name;
  final String? description;
  final String type;
  final String typeLabel;
  final int pointsRequired;
  final int? value;
  final String? image;
  final RewardCourseInfo? course;
  final bool canRedeem;

  factory RewardItem.fromJson(Map<String, dynamic> json) {
    final courseData = json['course'] as Map<String, dynamic>?;
    return RewardItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] ?? '').toString(),
      description: json['description']?.toString(),
      type: (json['type'] ?? '').toString(),
      typeLabel: (json['type_label'] ?? '').toString(),
      pointsRequired: (json['points_required'] as num?)?.toInt() ?? 0,
      value: (json['value'] as num?)?.toInt(),
      image: json['image']?.toString(),
      course: courseData != null ? RewardCourseInfo.fromJson(courseData) : null,
      canRedeem: (json['can_redeem'] as bool?) ?? false,
    );
  }
}

class RewardCourseInfo {
  RewardCourseInfo({required this.id, required this.title, required this.slug, this.thumbnail});

  final int id;
  final String title;
  final String slug;
  final String? thumbnail;

  factory RewardCourseInfo.fromJson(Map<String, dynamic> json) {
    return RewardCourseInfo(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      thumbnail: json['thumbnail']?.toString(),
    );
  }
}

class RewardsResponse {
  RewardsResponse({required this.rewards, this.needsAuth = false});
  final List<RewardItem> rewards;
  final bool needsAuth;
}

class RedeemResult {
  RedeemResult({
    required this.success,
    required this.message,
    this.code,
    this.newAvailablePoints,
  });

  final bool success;
  final String message;
  final String? code;
  final int? newAvailablePoints;
}

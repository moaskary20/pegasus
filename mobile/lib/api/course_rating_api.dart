import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class CourseRatingApi {
  CourseRatingApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// تقييم الدورة (نجوم + مراجعة اختيارية)
  static Future<CourseRatingResult?> rateCourse(String courseSlug, int stars, {String? review}) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/rate');
      final headers = {..._headers, 'Content-Type': 'application/json'};
      final body = jsonEncode({
        'stars': stars,
        if (review != null && review.trim().isNotEmpty) 'review': review.trim(),
      });
      final res = await http.post(uri, headers: headers, body: body);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return CourseRatingResult(
          success: (data['success'] as bool?) ?? true,
          message: (data['message'] ?? '').toString(),
        );
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class CourseRatingResult {
  CourseRatingResult({required this.success, required this.message});
  final bool success;
  final String message;
}

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class CertificateApi {
  CertificateApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// جلب رابط الشهادة (يُنشئ الملف إذا لم يكن موجوداً)
  static Future<CertificateUrlResult?> getCertificateUrl(String courseSlug) async {
    try {
      final uri = Uri.parse('$apiBaseUrl/api/courses/$courseSlug/certificate');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return CertificateUrlResult(
          url: (data['url'] ?? '').toString(),
          filename: (data['filename'] ?? 'certificate.pdf').toString(),
        );
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class CertificateUrlResult {
  CertificateUrlResult({required this.url, required this.filename});
  final String url;
  final String filename;
}

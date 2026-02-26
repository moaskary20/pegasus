import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';

class SupportApi {
  SupportApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json', 'Content-Type': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  static Future<SupportSettingsResponse> getSettings() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSupport');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return SupportSettingsResponse(
          supportEmail: (data['support_email'] ?? '').toString(),
          supportPhone: (data['support_phone'] ?? '').toString(),
          supportPhone2: (data['support_phone_2'] ?? '').toString(),
        );
      }
      return SupportSettingsResponse(supportEmail: '', supportPhone: '', supportPhone2: '');
    } catch (_) {
      return SupportSettingsResponse(supportEmail: '', supportPhone: '', supportPhone2: '');
    }
  }

  static Future<SupportSubmitResult> submitComplaint({
    required String name,
    required String email,
    String? phone,
    required String subject,
    required String message,
  }) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSupportComplaint');
      final body = jsonEncode({
        'name': name,
        'email': email,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
        'subject': subject,
        'message': message,
      });
      final res = await http.post(uri, headers: _headers, body: body);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200 || res.statusCode == 201) {
        return SupportSubmitResult(success: true, message: (data['message'] ?? 'تم الإرسال بنجاح').toString());
      }
      final msg = (data['message'] as String?) ?? data['errors']?.toString() ?? 'حدث خطأ';
      return SupportSubmitResult(success: false, message: msg.toString());
    } catch (e) {
      return SupportSubmitResult(success: false, message: 'تحقق من الاتصال بالإنترنت');
    }
  }

  static Future<SupportSubmitResult> submitContact({
    required String name,
    required String email,
    String? phone,
    required String subject,
    required String message,
  }) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSupportContact');
      final body = jsonEncode({
        'name': name,
        'email': email,
        if (phone != null && phone.isNotEmpty) 'phone': phone,
        'subject': subject,
        'message': message,
      });
      final res = await http.post(uri, headers: _headers, body: body);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200 || res.statusCode == 201) {
        return SupportSubmitResult(success: true, message: (data['message'] ?? 'تم الإرسال بنجاح').toString());
      }
      final msg = (data['message'] as String?) ?? data['errors']?.toString() ?? 'حدث خطأ';
      return SupportSubmitResult(success: false, message: msg.toString());
    } catch (e) {
      return SupportSubmitResult(success: false, message: 'تحقق من الاتصال بالإنترنت');
    }
  }

  /// جلب شكاوى واستفسارات المستخدم السابقة
  static Future<List<MyComplaintItem>> getMyComplaints() async {
    try {
      if (AuthApi.token == null) return [];
      final uri = Uri.parse('$apiBaseUrl$apiSupportMyComplaints');
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final list = (data['complaints'] as List<dynamic>?) ?? [];
        return list.map((e) => MyComplaintItem.fromJson(e as Map<String, dynamic>)).toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }
}

class MyComplaintItem {
  MyComplaintItem({
    required this.id,
    required this.type,
    required this.typeLabel,
    required this.subject,
    required this.message,
    required this.status,
    required this.statusLabel,
    required this.createdAt,
  });
  final int id;
  final String type;
  final String typeLabel;
  final String subject;
  final String message;
  final String status;
  final String statusLabel;
  final String createdAt;

  factory MyComplaintItem.fromJson(Map<String, dynamic> json) {
    return MyComplaintItem(
      id: (json['id'] as num).toInt(),
      type: (json['type'] ?? '').toString(),
      typeLabel: (json['type_label'] ?? '').toString(),
      subject: (json['subject'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      status: (json['status'] ?? '').toString(),
      statusLabel: (json['status_label'] ?? '').toString(),
      createdAt: (json['created_at'] ?? '').toString(),
    );
  }
}

class SupportSettingsResponse {
  SupportSettingsResponse({
    required this.supportEmail,
    required this.supportPhone,
    required this.supportPhone2,
  });
  final String supportEmail;
  final String supportPhone;
  final String supportPhone2;
}

class SupportSubmitResult {
  SupportSubmitResult({required this.success, required this.message});
  final bool success;
  final String message;
}

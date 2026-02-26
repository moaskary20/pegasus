import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/error_messages.dart';

const _tokenKey = 'auth_token';
const _rememberKey = 'auth_remember';

class AuthApi {
  AuthApi._();

  static String? _token;
  static bool _remember = true;

  static String? get token => _token;

  static Future<void> loadStoredToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    _remember = prefs.getBool(_rememberKey) ?? true;
  }

  static Future<void> saveToken(String? t, {bool? remember}) async {
    _token = t;
    if (remember != null) _remember = remember;
    final prefs = await SharedPreferences.getInstance();
    if (t != null) {
      await prefs.setString(_tokenKey, t);
      await prefs.setBool(_rememberKey, _remember);
    } else {
      await prefs.remove(_tokenKey);
      await prefs.remove(_rememberKey);
    }
  }

  static Map<String, String> get _headers {
    final h = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (_token != null) h['Authorization'] = 'Bearer $_token';
    return h;
  }

  static Future<AuthResult> login({
    required String email,
    required String password,
    bool remember = true,
  }) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiAuthLogin');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );
      Map<String, dynamic> data = {};
      try {
        data = jsonDecode(res.body is String ? res.body : res.body.toString()) as Map<String, dynamic>? ?? {};
      } catch (_) {
        if (res.statusCode != 200) {
          return AuthResult.failure(
            message: ErrorMessages.from(null, statusCode: res.statusCode),
            error: res.body.length > 300 ? '${res.body.substring(0, 300)}...' : res.body,
          );
        }
      }
      if (res.statusCode == 200) {
        final token = data['token'] as String?;
        final user = data['user'] as Map<String, dynamic>?;
        if (token != null) {
          await saveToken(token, remember: remember);
          return AuthResult.success(token: token, user: user);
        }
      }
      final message = data['message'] as String? ?? ErrorMessages.from(null, statusCode: res.statusCode);
      final errors = data['errors'] as Map<String, dynamic>?;
      return AuthResult.failure(message: message, errors: errors, error: 'HTTP ${res.statusCode}: ${res.body.length > 200 ? "${res.body.substring(0, 200)}..." : res.body}');
    } catch (e) {
      return AuthResult.failure(
        message: ErrorMessages.from(e),
        error: e.toString(),
      );
    }
  }

  static Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required String userType,
  }) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiAuthRegister');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'user_type': userType,
        }),
      );
      final data = jsonDecode(res.body is String ? res.body : res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200 || res.statusCode == 201) {
        final token = data['token'] as String?;
        final user = data['user'] as Map<String, dynamic>?;
        if (token != null) {
          await saveToken(token, remember: true);
          return AuthResult.success(token: token, user: user);
        }
      }
      final message = data['message'] as String? ?? ErrorMessages.from(null, statusCode: res.statusCode);
      final errors = data['errors'] as Map<String, dynamic>?;
      return AuthResult.failure(message: message, errors: errors);
    } catch (e) {
      return AuthResult.failure(message: ErrorMessages.from(e), error: e.toString());
    }
  }

  /// جلب بيانات المستخدم الحالي من الـ backend (للسايدبار والملف الشخصي)
  static Future<Map<String, dynamic>?> getUser() async {
    if (_token == null) await loadStoredToken();
    if (_token == null) return null;
    try {
      final uri = Uri.parse('$apiBaseUrl$apiAuthUser');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>?;
        return data;
      }
    } catch (_) {}
    return null;
  }

  /// تحديث الملف الشخصي (اسم، بريد، هاتف، مدينة، عمل، صورة)
  static Future<AuthResult> updateProfile({
    required String name,
    required String email,
    String? phone,
    String? city,
    String? job,
    File? avatarFile,
  }) async {
    if (_token == null) {
      return AuthResult.failure(message: 'يجب تسجيل الدخول أولاً');
    }
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiBaseUrl$apiAuthUser'),
      );
      request.headers['Accept'] = 'application/json';
      request.headers['Authorization'] = 'Bearer $_token';

      request.fields['name'] = name;
      request.fields['email'] = email;
      request.fields['_method'] = 'PUT';
      if (phone != null) request.fields['phone'] = phone;
      if (city != null) request.fields['city'] = city;
      if (job != null) request.fields['job'] = job;
      if (avatarFile != null) {
        request.files.add(await http.MultipartFile.fromPath(
          'avatar',
          avatarFile.path,
        ));
      }

      final streamedResponse = await request.send();
      final res = await http.Response.fromStream(streamedResponse);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};

      if (res.statusCode == 200) {
        return AuthResult.success(
          user: data['user'] as Map<String, dynamic>?,
        );
      }
      final message = data['message'] as String? ?? 'حدث خطأ (${res.statusCode})';
      final errors = data['errors'] as Map<String, dynamic>?;
      return AuthResult.failure(message: message, errors: errors);
    } catch (e) {
      return AuthResult.failure(
        message: 'تحقق من الاتصال بالإنترنت وحاول مرة أخرى.',
        error: e.toString(),
      );
    }
  }

  /// تغيير كلمة المرور
  static Future<AuthResult> updatePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    if (_token == null) {
      return AuthResult.failure(message: 'يجب تسجيل الدخول أولاً');
    }
    try {
      final uri = Uri.parse('$apiBaseUrl$apiAuthPassword');
      final res = await http.put(
        uri,
        headers: _headers,
        body: jsonEncode({
          'current_password': currentPassword,
          'password': newPassword,
          'password_confirmation': newPassword,
        }),
      );
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        return AuthResult.success();
      }
      final message = data['message'] as String? ?? 'حدث خطأ (${res.statusCode})';
      final errors = data['errors'] as Map<String, dynamic>?;
      return AuthResult.failure(message: message, errors: errors);
    } catch (e) {
      return AuthResult.failure(
        message: 'تحقق من الاتصال بالإنترنت وحاول مرة أخرى.',
        error: e.toString(),
      );
    }
  }

  static Future<void> logout() async {
    try {
      if (_token != null) {
        final uri = Uri.parse('$apiBaseUrl$apiAuthLogout');
        await http.post(uri, headers: _headers);
      }
    } catch (_) {}
    await saveToken(null);
  }
}

class AuthResult {
  AuthResult._({this.token, this.user, this.message, this.errors, this.error});

  final String? token;
  final Map<String, dynamic>? user;
  final String? message;
  final Map<String, dynamic>? errors;
  final String? error;

  factory AuthResult.success({String? token, Map<String, dynamic>? user}) {
    return AuthResult._(token: token, user: user);
  }

  factory AuthResult.failure({
    String? message,
    Map<String, dynamic>? errors,
    String? error,
  }) {
    return AuthResult._(message: message, errors: errors, error: error);
  }

  bool get isSuccess => message == null && errors == null && error == null;
  bool get isFailure => !isSuccess;

  /// أول رسالة خطأ من الحقل المحدد أو الرسالة العامة
  String? get firstFieldError {
    if (errors == null) return message;
    for (final v in errors!.values) {
      if (v is List && v.isNotEmpty && v.first is String) return v.first as String;
      if (v is String) return v;
    }
    return message;
  }

  /// أخطاء حقول (مثلاً email: ['الرسالة'])
  Map<String, String> get fieldErrors {
    final out = <String, String>{};
    if (errors == null) return out;
    for (final e in errors!.entries) {
      final key = e.key as String;
      final v = e.value;
      if (v is List && v.isNotEmpty) out[key] = (v.first as String?) ?? '';
      if (v is String) out[key] = v;
    }
    return out;
  }
}

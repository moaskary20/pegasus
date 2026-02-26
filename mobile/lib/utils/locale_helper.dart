import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String _keyLocale = 'app_locale';

/// حفظ واسترجاع لغة التطبيق (عربي / English)
class LocaleHelper {
  static Future<Locale> getLocale() async {
    final prefs = await SharedPreferences.getInstance();
    final code = prefs.getString(_keyLocale) ?? 'ar';
    return Locale(code);
  }

  static Future<void> setLocale(String languageCode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyLocale, languageCode);
  }
}

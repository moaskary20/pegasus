import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String _keyLocale = 'app_locale';

/// لغة التطبيق العربية فقط.
class LocaleHelper {
  static Future<Locale> getLocale() async {
    final prefs = await SharedPreferences.getInstance();
    final code = prefs.getString(_keyLocale);
    if (code != null && code != 'ar') {
      await prefs.setString(_keyLocale, 'ar');
    }
    return const Locale('ar');
  }

  static Future<void> setLocale(String languageCode) async {
    if (languageCode != 'ar') return;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyLocale, 'ar');
  }
}

import 'package:flutter/material.dart';

/// ألوان وثيم التطبيق مع حركات متقدمة
class AppTheme {
  AppTheme._();

  static const Color primary = Color(0xFF2c004d);
  static const Color primaryLight = Color(0xFF4a1a6d);
  static const Color primaryDark = Color(0xFF1a0030);
  static const Color accent = Color(0xFF7c3aed);
  static const Color surface = Color(0xFFF8F6FA);
  static const Color error = Color(0xFFB00020);

  static const Duration animFast = Duration(milliseconds: 200);
  static const Duration animNormal = Duration(milliseconds: 350);
  static const Duration animSlow = Duration(milliseconds: 500);
  static const Curve curveDefault = Curves.easeOutCubic;
  static const Curve curveEmphasized = Curves.easeInOutBack;

  static List<Color> get gradientPrimary => [primary, primaryLight];
  static List<Color> get gradientAccent => [accent, primary];

  /// انيميشن ظهور عنصر في القائمة (تأخير حسب الفهرس)
  static Tween<Offset> slideInTween(int index) {
    return Tween<Offset>(
      begin: const Offset(0.15, 0),
      end: Offset.zero,
    );
  }

  static Tween<double> fadeInTween() => Tween<double>(begin: 0, end: 1);
  static Tween<double> scaleInTween() => Tween<double>(begin: 0.92, end: 1);
}

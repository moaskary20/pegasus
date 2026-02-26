import 'package:flutter/material.dart';

/// لتوفير تغيير اللغة للشجرة — يستخدم من MyApp
class AppLocaleScope extends InheritedWidget {
  const AppLocaleScope({
    super.key,
    required this.locale,
    required this.setLocale,
    required super.child,
  });

  final Locale locale;
  final void Function(Locale) setLocale;

  static AppLocaleScope of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<AppLocaleScope>();
    assert(scope != null, 'LocaleScope not found');
    return scope!;
  }

  @override
  bool updateShouldNotify(AppLocaleScope old) =>
      locale != old.locale;
}

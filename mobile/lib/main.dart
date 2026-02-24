import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'api/auth_api.dart';
import 'screens/main_shell.dart';
import 'screens/login_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  if (kDebugMode) {
    FlutterError.onError = (details) {
      FlutterError.presentError(details);
      debugPrint('FlutterError: ${details.exception}');
    };
  }
  runZonedGuarded(() {
    runApp(const MyApp());
  }, (error, stack) {
    if (kDebugMode) {
      debugPrint('Async error: $error');
      debugPrint('$stack');
    }
  });
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  /// ثيم بسيط بدون تحميل خطوط من الشبكة لتفادي شاشة بيضاء على الويب
  static ThemeData _buildTheme() {
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF2c004d)),
      useMaterial3: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'أكاديمية بيغاسوس',
      debugShowCheckedModeBanner: false,
      locale: const Locale('ar'),
      builder: (context, child) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: child ?? const SizedBox.shrink(),
        );
      },
      theme: _buildTheme(),
      home: const SplashScreen(),
    );
  }
}

/// شاشة الـ Splash تعرض صورة الشعار ثم تنتقل لتسجيل الدخول أو الصفحة الرئيسية إن وُجد توكن
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    // تأجيل التنقّل حتى بعد رسم الإطار الأول لتفادي شاشة بيضاء على الويب
    WidgetsBinding.instance.addPostFrameCallback((_) => _navigate());
  }

  Future<void> _navigate() async {
    try {
      await AuthApi.loadStoredToken();
    } catch (e) {
      if (kDebugMode) debugPrint('loadStoredToken error: $e');
    }
    await Future.delayed(const Duration(seconds: 3));
    if (!mounted) return;
    try {
      if (AuthApi.token != null) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => const MainShell(),
          ),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => const LoginScreen(),
          ),
        );
      }
    } catch (e) {
      if (kDebugMode) debugPrint('Navigate error: $e');
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => const LoginScreen(),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        color: const Color(0xFF2c004d),
        child: Image.asset(
          'assets/images/splash.png',
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => const Center(
            child: Text(
              'أكاديمية بيغاسوس',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

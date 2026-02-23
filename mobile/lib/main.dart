import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'api/auth_api.dart';
import 'screens/main_shell.dart';
import 'screens/login_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

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
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF2c004d)),
        useMaterial3: true,
        fontFamily: 'Tajawal',
        textTheme: GoogleFonts.tajawalTextTheme(ThemeData.light().textTheme),
        primaryTextTheme: GoogleFonts.tajawalTextTheme(ThemeData.light().primaryTextTheme),
        appBarTheme: AppBarTheme(
          titleTextStyle: GoogleFonts.tajawal(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ),
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
    _navigate();
  }

  Future<void> _navigate() async {
    await AuthApi.loadStoredToken();
    await Future.delayed(const Duration(seconds: 3));
    if (!mounted) return;
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
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          color: Color(0xFF2c004d),
        ),
        child: Image.asset(
          'assets/images/splash.png',
          fit: BoxFit.cover,
        ),
      ),
    );
  }
}

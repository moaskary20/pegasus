import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:video_player/video_player.dart';
import 'api/auth_api.dart';
import 'screens/main_shell.dart';
import 'screens/login_screen.dart';
import 'utils/locale_helper.dart';
import 'app_locale.dart';
import 'services/local_notifications_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await LocalNotificationsService.init();
  if (kDebugMode) {
    FlutterError.onError = (details) {
      FlutterError.presentError(details);
      debugPrint('FlutterError: ${details.exception}');
    };
  }
  final locale = await LocaleHelper.getLocale();
  runApp(MyApp(initialLocale: locale));
}

class MyApp extends StatefulWidget {
  const MyApp({super.key, this.initialLocale = const Locale('ar')});

  final Locale initialLocale;

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  late Locale _locale;

  @override
  void initState() {
    super.initState();
    _locale = widget.initialLocale;
  }

  void setLocale(Locale locale) {
    LocaleHelper.setLocale(locale.languageCode);
    setState(() => _locale = locale);
  }

  /// ثيم باستخدام خط Tajawal من Google Fonts لجميع النصوص في التطبيق
  static ThemeData _buildTheme() {
    const primary = Color(0xFF2c004d);
    final base = ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: primary),
      useMaterial3: true,
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: _SlideUpwardsPageTransitionsBuilder(),
          TargetPlatform.iOS: _SlideUpwardsPageTransitionsBuilder(),
        },
      ),
    );
    return base.copyWith(
      textTheme: GoogleFonts.tajawalTextTheme(base.textTheme),
      primaryTextTheme: GoogleFonts.tajawalTextTheme(base.primaryTextTheme),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = _buildTheme();
    final isArabic = _locale.languageCode == 'ar';
    return MaterialApp(
      title: 'أكاديمية بيغاسوس',
      debugShowCheckedModeBanner: false,
      locale: _locale,
      builder: (context, child) {
        return AppLocaleScope(
          locale: _locale,
          setLocale: setLocale,
          child: Directionality(
            textDirection: isArabic ? TextDirection.rtl : TextDirection.ltr,
            child: DefaultTextStyle(
              style: GoogleFonts.tajawal(),
              child: child ?? const SizedBox.shrink(),
            ),
          ),
        );
      },
      theme: theme,
      home: const SplashScreen(),
    );
  }
}

class _SlideUpwardsPageTransitionsBuilder extends PageTransitionsBuilder {
  const _SlideUpwardsPageTransitionsBuilder();

  @override
  Widget buildTransitions<T>(
    PageRoute<T> route,
    BuildContext context,
    Animation<double> animation,
    Animation<double> secondaryAnimation,
    Widget child,
  ) {
    final curved = CurvedAnimation(parent: animation, curve: Curves.easeOutCubic);
    return SlideTransition(
      position: Tween<Offset>(begin: const Offset(0, 0.05), end: Offset.zero).animate(curved),
      child: FadeTransition(opacity: curved, child: child),
    );
  }
}

/// شاشة الـ Splash تعرض فيديو splash.mp4 يملأ الشاشة ثم تنتقل لتسجيل الدخول أو الرئيسية
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  VideoPlayerController? _controller;
  Timer? _navigateTimer;
  bool _navigated = false;

  @override
  void initState() {
    super.initState();
    _prepareAndPlayVideo();
  }

  Future<void> _prepareAndPlayVideo() async {
    // لا يُعرض شيء سوى الفيديو — شاشة سوداء حتى جاهزية الفيديو
    _controller = VideoPlayerController.asset('assets/images/splash.mp4');
    try {
      await _controller!.initialize();
      if (!mounted) return;
      _controller!.setVolume(0); // عرض بدون صوت
      _controller!.setLooping(false);
      _controller!.addListener(_onVideoStatus);
      await _controller!.play();
      if (!mounted) return;
      setState(() {});
      // تعبئة التوكن في الخلفية للتنقل
      AuthApi.loadStoredToken().catchError((e) {
        if (kDebugMode) debugPrint('loadStoredToken error: $e');
      });
      // حد أقصى 8 ثوانٍ في حال فشل الفيديو أو لم ينتهِ
      _navigateTimer = Timer(const Duration(seconds: 8), _navigate);
    } catch (e) {
      if (kDebugMode) debugPrint('Splash video init error: $e');
      if (mounted) {
        AuthApi.loadStoredToken().catchError((_) {});
        _navigateTimer = Timer(const Duration(seconds: 2), _navigate);
        setState(() {});
      }
    }
  }

  void _onVideoStatus() {
    if (_controller == null || _navigated) return;
    if (_controller!.value.position >= _controller!.value.duration &&
        _controller!.value.duration.inMilliseconds > 0) {
      _navigate();
    }
  }

  Future<void> _navigate() async {
    if (_navigated) return;
    _navigated = true;
    _navigateTimer?.cancel();
    _controller?.removeListener(_onVideoStatus);
    try {
      await AuthApi.loadStoredToken();
    } catch (_) {}
    if (!mounted) return;
    try {
      if (AuthApi.token != null) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const MainShell()),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
      }
    } catch (e) {
      if (kDebugMode) debugPrint('Navigate error: $e');
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  void dispose() {
    _navigateTimer?.cancel();
    _controller?.removeListener(_onVideoStatus);
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SizedBox(
        width: double.infinity,
        height: double.infinity,
        child: _controller != null && _controller!.value.isInitialized
            ? FittedBox(
                fit: BoxFit.cover,
                child: SizedBox(
                  width: _controller!.value.size.width,
                  height: _controller!.value.size.height,
                  child: VideoPlayer(_controller!),
                ),
              )
            : Container(color: Colors.black),
      ),
    );
  }
}

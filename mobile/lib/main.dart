import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
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
  runApp(const MyApp());
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

/// شاشة الـ Splash تعرض فيديو spalsh.mp4 (4 ثوانٍ) يملأ الشاشة ثم تنتقل لتسجيل الدخول أو الرئيسية
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
    _initVideo();
    WidgetsBinding.instance.addPostFrameCallback((_) => _startNavigateTimer());
  }

  Future<void> _initVideo() async {
    _controller = VideoPlayerController.asset('assets/images/spalsh.mp4');
    try {
      await _controller!.initialize();
      if (!mounted) return;
      _controller!.setLooping(false);
      _controller!.play();
      _controller!.addListener(_onVideoStatus);
      setState(() {}); // إعادة الرسم لعرض الفيديو بدل النص
    } catch (e) {
      if (kDebugMode) debugPrint('Splash video init error: $e');
      if (mounted) setState(() {});
    }
  }

  void _onVideoStatus() {
    if (_controller == null || _navigated) return;
    if (_controller!.value.position >= _controller!.value.duration &&
        _controller!.value.duration.inMilliseconds > 0) {
      _navigate();
    }
  }

  void _startNavigateTimer() async {
    try {
      await AuthApi.loadStoredToken();
    } catch (e) {
      if (kDebugMode) debugPrint('loadStoredToken error: $e');
    }
    _navigateTimer = Timer(const Duration(seconds: 4), _navigate);
  }

  void _navigate() {
    if (_navigated) return;
    _navigated = true;
    _navigateTimer?.cancel();
    _controller?.removeListener(_onVideoStatus);
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
            : Container(
                color: const Color(0xFF2c004d),
                child: const Center(
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

import 'package:flutter/material.dart';
import '../api/auth_api.dart';
import '../app_theme.dart';
import 'main_shell.dart';
import 'register_screen.dart';

const Color _primary = Color(0xFF2c004d);
const Color _primaryLight = Color(0xFF4a1a6d);
const Color _accent = Color(0xFF7c3aed);

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with TickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _rememberMe = true;
  bool _obscurePassword = true;
  bool _isLoading = false;
  late AnimationController _logoAnimController;
  late AnimationController _formAnimController;
  late Animation<double> _logoScale;
  late Animation<double> _logoOpacity;

  @override
  void initState() {
    super.initState();
    _logoAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..forward();

    _formAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );

    _logoScale = Tween<double>(begin: 0.3, end: 1.0).animate(
      CurvedAnimation(
        parent: _logoAnimController,
        curve: const Interval(0, 0.6, curve: Curves.elasticOut),
      ),
    );

    _logoOpacity = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(
        parent: _logoAnimController,
        curve: const Interval(0, 0.5, curve: Curves.easeOut),
      ),
    );

    Future.delayed(const Duration(milliseconds: 400), () {
      if (mounted) _formAnimController.forward();
    });
  }

  @override
  void dispose() {
    _logoAnimController.dispose();
    _formAnimController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _onLogin() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _isLoading = true);
    try {
      final result = await AuthApi.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
        remember: _rememberMe,
      );
      if (!mounted) return;
      if (result.isSuccess) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const MainShell()),
        );
      } else {
        setState(() => _isLoading = false);
        final msg = result.firstFieldError ?? result.message ?? 'فشل تسجيل الدخول';
        final extra = result.error != null && result.error!.isNotEmpty ? '\n${result.error}' : '';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('$msg$extra'),
            backgroundColor: Colors.red.shade700,
            duration: const Duration(seconds: 5),
            behavior: SnackBarBehavior.floating,
          ),
        );
        _formKey.currentState?.validate();
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _goToRegister() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const RegisterScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          _buildBackground(),
          SafeArea(
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const SizedBox(height: 32),
                      _buildLogoSection(),
                      const SizedBox(height: 32),
                      _buildFormCard(),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBackground() {
    return Container(
      width: double.infinity,
      height: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [
            Color(0xFFF8F6FA),
            Color(0xFFEDE9F2),
            Color(0xFFE8E2F0),
          ],
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            top: -80,
            right: -80,
            child: _FloatingCircle(
              size: 200,
              color: _primary.withValues(alpha: 0.06),
              delay: 0,
            ),
          ),
          Positioned(
            top: 120,
            left: -60,
            child: _FloatingCircle(
              size: 140,
              color: _accent.withValues(alpha: 0.08),
              delay: 200,
            ),
          ),
          Positioned(
            bottom: 180,
            right: -40,
            child: _FloatingCircle(
              size: 100,
              color: _primaryLight.withValues(alpha: 0.05),
              delay: 400,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLogoSection() {
    return AnimatedBuilder(
      animation: _logoAnimController,
      builder: (context, child) {
        return Opacity(
          opacity: _logoOpacity.value,
          child: Transform.scale(
            scale: _logoScale.value,
            child: Column(
              children: [
                Container(
                  decoration: BoxDecoration(
                    boxShadow: [
                      BoxShadow(
                        color: _primary.withValues(alpha: 0.2),
                        blurRadius: 24,
                        offset: const Offset(0, 8),
                        spreadRadius: 0,
                      ),
                      BoxShadow(
                        color: _primary.withValues(alpha: 0.08),
                        blurRadius: 40,
                        offset: const Offset(0, 16),
                      ),
                    ],
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(24),
                    child: Image.asset(
                      'assets/images/logo.png',
                      height: 100,
                      width: 100,
                      fit: BoxFit.contain,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildFormCard() {
    return FadeTransition(
      opacity: CurvedAnimation(
        parent: _formAnimController,
        curve: Curves.easeOut,
      ),
      child: SlideTransition(
        position: Tween<Offset>(
          begin: const Offset(0, 0.15),
          end: Offset.zero,
        ).animate(CurvedAnimation(
          parent: _formAnimController,
          curve: Curves.easeOutCubic,
        )),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: _primary.withValues(alpha: 0.06),
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'تسجيل الدخول',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: _primary,
                    ),
                textAlign: TextAlign.center,
                textDirection: TextDirection.rtl,
              ),
              const SizedBox(height: 24),
              _buildAnimatedField(
                delay: 0.1,
                child: TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  textDirection: TextDirection.ltr,
                  enabled: !_isLoading,
                  decoration: InputDecoration(
                    labelText: 'البريد الإلكتروني',
                    hintText: 'example@email.com',
                    prefixIcon: Icon(Icons.email_outlined, color: _primary.withValues(alpha: 0.7)),
                    filled: true,
                    fillColor: Colors.grey.shade50,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: _primary, width: 2),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide(color: Colors.grey.shade200),
                    ),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return 'أدخل البريد الإلكتروني';
                    if (!v.contains('@')) return 'بريد إلكتروني غير صالح';
                    return null;
                  },
                ),
              ),
              const SizedBox(height: 16),
              _buildAnimatedField(
                delay: 0.2,
                child: TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  textDirection: TextDirection.ltr,
                  enabled: !_isLoading,
                  decoration: InputDecoration(
                    labelText: 'كلمة المرور',
                    hintText: '••••••••',
                    prefixIcon: Icon(Icons.lock_outline, color: _primary.withValues(alpha: 0.7)),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                        color: Colors.grey.shade600,
                      ),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                    filled: true,
                    fillColor: Colors.grey.shade50,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: _primary, width: 2),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide(color: Colors.grey.shade200),
                    ),
                  ),
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) return 'أدخل كلمة المرور';
                    return null;
                  },
                ),
              ),
              const SizedBox(height: 12),
              _buildAnimatedField(
                delay: 0.3,
                child: Row(
                  children: [
                    SizedBox(
                      height: 24,
                      width: 24,
                      child: Checkbox(
                        value: _rememberMe,
                        onChanged: _isLoading ? null : (v) => setState(() => _rememberMe = v ?? false),
                        fillColor: WidgetStateProperty.resolveWith((states) {
                          if (states.contains(WidgetState.selected)) return _primary;
                          return Colors.grey;
                        }),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                      ),
                    ),
                    const SizedBox(width: 10),
                    GestureDetector(
                      onTap: _isLoading ? null : () => setState(() => _rememberMe = !_rememberMe),
                      child: Text(
                        'تذكرني',
                        style: TextStyle(
                          color: Colors.grey.shade700,
                          fontSize: 14,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              _buildAnimatedField(
                delay: 0.4,
                child: FilledButton(
                  onPressed: _isLoading ? null : _onLogin,
                  style: FilledButton.styleFrom(
                    backgroundColor: _primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 0,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          height: 24,
                          width: 24,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('تسجيل الدخول'),
                ),
              ),
              const SizedBox(height: 12),
              _buildAnimatedField(
                delay: 0.5,
                child: OutlinedButton.icon(
                  onPressed: _isLoading ? null : _goToRegister,
                  icon: const Icon(Icons.person_add_outlined, size: 20),
                  label: const Text('إنشاء حساب جديد'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _primary,
                    side: const BorderSide(color: _primary),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAnimatedField({required double delay, required Widget child}) {
    return AnimatedBuilder(
      animation: _formAnimController,
      builder: (context, _) {
        final t = ((_formAnimController.value - delay) / (1 - delay)).clamp(0.0, 1.0);
        final curved = Curves.easeOutCubic.transform(t);
        return Opacity(
          opacity: curved,
          child: Transform.translate(
            offset: Offset(0, 16 * (1 - curved)),
            child: child,
          ),
        );
      },
    );
  }
}

class _FloatingCircle extends StatefulWidget {
  const _FloatingCircle({
    required this.size,
    required this.color,
    this.delay = 0,
  });

  final double size;
  final Color color;
  final int delay;

  @override
  State<_FloatingCircle> createState() => _FloatingCircleState();
}

class _FloatingCircleState extends State<_FloatingCircle>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 4),
    )..repeat(reverse: true);

    _animation = Tween<double>(begin: 0.95, end: 1.05).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );

    if (widget.delay > 0) {
      Future.delayed(Duration(milliseconds: widget.delay), () {
        if (mounted) _controller.forward();
      });
    } else {
      _controller.forward();
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (context, child) {
        return Transform.scale(
          scale: _animation.value,
          child: Container(
            width: widget.size,
            height: widget.size,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: widget.color,
            ),
          ),
        );
      },
    );
  }
}

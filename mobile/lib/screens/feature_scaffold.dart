import 'package:flutter/material.dart';
import '../app_theme.dart';

/// شاشة ميزة بعنوان وزر رجوع مع حركات ظهور
class FeatureScaffold extends StatelessWidget {
  const FeatureScaffold({
    super.key,
    required this.title,
    required this.body,
    this.onBack,
    this.actions,
    this.floatingActionButton,
  });

  final String title;
  final Widget body;
  final VoidCallback? onBack;
  final List<Widget>? actions;
  final Widget? floatingActionButton;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      floatingActionButton: floatingActionButton,
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 0,
            pinned: true,
            backgroundColor: AppTheme.primary,
            foregroundColor: Colors.white,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded),
              onPressed: onBack ?? () => Navigator.maybePop(context),
            ),
            title: TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              curve: AppTheme.curveDefault,
              builder: (context, value, child) => Opacity(
                opacity: value,
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
            actions: actions,
          ),
          SliverToBoxAdapter(
            child: TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              curve: AppTheme.curveDefault,
              builder: (context, value, child) => Opacity(
                opacity: value,
                child: Transform.translate(
                  offset: Offset(0, 20 * (1 - value)),
                  child: child,
                ),
              ),
              child: body,
            ),
          ),
        ],
      ),
    );
  }
}

/// بطاقة قائمة مع حركة انزلاق وتأخير حسب الفهرس
class AnimatedListCard extends StatelessWidget {
  const AnimatedListCard({
    super.key,
    required this.child,
    this.index = 0,
    this.onTap,
  });

  final Widget child;
  final int index;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      key: ValueKey('card_$index'),
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 280 + (index * 40).clamp(0, 200)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) {
        return Opacity(
          opacity: value,
          child: Transform.translate(
            offset: Offset(0, 24 * (1 - value)),
            child: Material(
              color: Colors.white,
              elevation: 0,
              shadowColor: AppTheme.primary.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(16),
              child: InkWell(
                onTap: onTap,
                borderRadius: BorderRadius.circular(16),
                child: child,
              ),
            ),
          ),
        );
      },
    );
  }
}

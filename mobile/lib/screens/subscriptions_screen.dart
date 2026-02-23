import 'package:flutter/material.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';

/// الاشتراكات — مطابق للـ backend (subscriptions)
class SubscriptionsScreen extends StatelessWidget {
  const SubscriptionsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'الاشتراكات',
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              builder: (context, value, _) => Opacity(
                opacity: value,
                child: Text(
                  'خطط الاشتراك والدورات المفعلة',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
                ),
              ),
            ),
            const SizedBox(height: 32),
            _AnimatedEmptyState(
              icon: Icons.card_membership_rounded,
              message: 'صفحة الاشتراكات',
              subtitle: 'ستظهر هنا خطط الاشتراك وسجل التجديد',
            ),
          ],
        ),
      ),
    );
  }
}

class _AnimatedEmptyState extends StatelessWidget {
  const _AnimatedEmptyState({
    required this.icon,
    required this.message,
    this.subtitle,
  });

  final IconData icon;
  final String message;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.scaleInTween(),
      duration: AppTheme.animSlow,
      curve: AppTheme.curveEmphasized,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.scale(
          scale: value,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(28),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
                ),
                const SizedBox(height: 24),
                Text(
                  message,
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                ),
                if (subtitle != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    subtitle!,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

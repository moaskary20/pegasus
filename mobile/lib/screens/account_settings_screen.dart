import 'package:flutter/material.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';

/// إعدادات الحساب — مطابق للـ backend (account: update + password)
class AccountSettingsScreen extends StatelessWidget {
  const AccountSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'إعدادات الحساب',
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
                  'تعديل البيانات الشخصية وكلمة المرور',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
                ),
              ),
            ),
            const SizedBox(height: 24),
            _AnimatedSettingTile(
              index: 0,
              icon: Icons.person_outline_rounded,
              title: 'الملف الشخصي',
              subtitle: 'الاسم، البريد، الصورة',
              onTap: () {},
            ),
            _AnimatedSettingTile(
              index: 1,
              icon: Icons.lock_outline_rounded,
              title: 'كلمة المرور',
              subtitle: 'تغيير كلمة المرور',
              onTap: () {},
            ),
            _AnimatedSettingTile(
              index: 2,
              icon: Icons.notifications_active_outlined,
              title: 'إعدادات الإشعارات',
              subtitle: 'تفعيل/إيقاف أنواع الإشعارات',
              onTap: () {},
            ),
            _AnimatedSettingTile(
              index: 3,
              icon: Icons.language_rounded,
              title: 'اللغة',
              subtitle: 'العربية',
              onTap: () {},
            ),
          ],
        ),
      ),
    );
  }
}

class _AnimatedSettingTile extends StatelessWidget {
  const _AnimatedSettingTile({
    required this.index,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final int index;
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 280 + (index * 45)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.translate(
          offset: Offset(0, 20 * (1 - value)),
          child: Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 0,
              child: InkWell(
                onTap: onTap,
                borderRadius: BorderRadius.circular(16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(icon, color: AppTheme.primary, size: 24),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              title,
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.primaryDark,
                                  ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              subtitle,
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                            ),
                          ],
                        ),
                      ),
                      Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey.shade400),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/auth_api.dart';
import 'feature_scaffold.dart';
import 'profile_edit_screen.dart';
import 'password_change_screen.dart';
import 'notification_settings_screen.dart';

/// إعدادات الحساب — بيانات المستخدم من الـ backend (GET /api/auth/user)
class AccountSettingsScreen extends StatefulWidget {
  const AccountSettingsScreen({super.key});

  @override
  State<AccountSettingsScreen> createState() => _AccountSettingsScreenState();
}

class _AccountSettingsScreenState extends State<AccountSettingsScreen> {
  Map<String, dynamic>? _user;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    final user = await AuthApi.getUser();
    if (mounted) setState(() { _user = user; _loading = false; });
  }

  @override
  Widget build(BuildContext context) {
    final name = _user?['name'] as String?;
    final email = _user?['email'] as String?;
    return FeatureScaffold(
      title: 'إعدادات الحساب',
      body: RefreshIndicator(
        onRefresh: _loadUser,
        color: AppTheme.primary,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_loading)
                const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator(color: AppTheme.primary)))
              else if (name != null || email != null) ...[
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                        child: Text(
                          (name != null && name.isNotEmpty) ? name.substring(0, 1).toUpperCase() : '?',
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.primary),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(name ?? '—', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold, color: AppTheme.primaryDark)),
                            if (email != null && email.isNotEmpty) Text(email, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
              ],
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
              subtitle: 'الاسم، البريد، الهاتف، المدينة، المهنة، الصورة',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const ProfileEditScreen()),
              ),
            ),
            _AnimatedSettingTile(
              index: 1,
              icon: Icons.lock_outline_rounded,
              title: 'كلمة المرور',
              subtitle: 'تغيير كلمة المرور',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const PasswordChangeScreen()),
              ),
            ),
            _AnimatedSettingTile(
              index: 2,
              icon: Icons.notifications_active_outlined,
              title: 'إعدادات الإشعارات',
              subtitle: 'عرض، تحديد كمقروء، حذف الإشعارات',
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const NotificationSettingsScreen()),
              ),
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

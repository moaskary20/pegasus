import 'package:flutter/material.dart';
import '../login_screen.dart';
import '../account_settings_screen.dart';
import '../support_screen.dart';
import '../notifications_reminders_screen.dart';
import '../subscriptions_screen.dart';
import '../purchase_history_screen.dart';
import '../instructor_finances_screen.dart';
import '../../api/auth_api.dart';
import '../../api/config.dart';
import '../widgets/app_header.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب حسابي — بيانات المستخدم من الـ backend وروابط الإعدادات والمساعدة
class AccountTab extends StatefulWidget {
  const AccountTab({
    super.key,
    this.onOpenDrawer,
    this.wishlistCount = 0,
    this.onWishlistCountChanged,
    this.onOpenFavorite,
    this.cartCount = 0,
    this.notificationsAndRemindersCount = 0,
    this.messagesCount = 0,
    this.onOpenCart,
    this.onOpenNotificationsAndReminders,
    this.onOpenMessages,
  });

  final VoidCallback? onOpenDrawer;
  final int wishlistCount;
  final void Function(int delta)? onWishlistCountChanged;
  final VoidCallback? onOpenFavorite;
  final int cartCount;
  final int notificationsAndRemindersCount;
  final int messagesCount;
  final VoidCallback? onOpenCart;
  final VoidCallback? onOpenNotificationsAndReminders;
  final VoidCallback? onOpenMessages;

  @override
  State<AccountTab> createState() => _AccountTabState();
}

class _AccountTabState extends State<AccountTab> {
  Map<String, dynamic>? _user;
  bool _loadingUser = true;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    await AuthApi.loadStoredToken();
    final user = await AuthApi.getUser();
    if (mounted) {
      setState(() {
        _user = user;
        _loadingUser = false;
      });
    }
  }

  bool get _isInstructor {
    final roles = _user?['roles'];
    if (roles is List) {
      return roles.any((r) => r.toString().toLowerCase() == 'instructor');
    }
    return false;
  }

  String _avatarUrl() {
    final url = _user?['avatar_url'] as String?;
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http')) return url;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return url.startsWith('/') ? '$base${url.substring(1)}' : '$base$url';
  }

  void _push(Widget screen) {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (context) => screen),
    ).then((_) => _loadUser());
  }

  @override
  Widget build(BuildContext context) {
    final name = _user?['name'] as String? ?? 'المستخدم';
    final email = _user?['email'] as String? ?? '';
    final avatarUrl = _avatarUrl();

    return Scaffold(
      appBar: AppHeader(
        title: 'حسابي',
        onMenu: widget.onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
        favoriteCount: widget.wishlistCount,
        onFavorite: widget.onOpenFavorite,
        cartCount: widget.cartCount,
        notificationsAndRemindersCount: widget.notificationsAndRemindersCount,
        messagesCount: widget.messagesCount,
        onCart: widget.onOpenCart,
        onNotificationsAndReminders: widget.onOpenNotificationsAndReminders,
        onMessages: widget.onOpenMessages,
      ),
      body: RefreshIndicator(
        onRefresh: _loadUser,
        color: _primary,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            const SizedBox(height: 16),
            Center(
              child: _loadingUser
                  ? SizedBox(
                      width: 96,
                      height: 96,
                      child: CircularProgressIndicator(color: _primary),
                    )
                  : ClipOval(
                      child: avatarUrl.isNotEmpty
                          ? Image.network(
                              avatarUrl,
                              width: 96,
                              height: 96,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) => _avatarPlaceholder(),
                            )
                          : _avatarPlaceholder(),
                    ),
            ),
            const SizedBox(height: 16),
            Center(
              child: Text(
                _loadingUser ? '...' : name,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: _primary,
                    ),
                textAlign: TextAlign.center,
                textDirection: TextDirection.rtl,
              ),
            ),
            if (email.isNotEmpty) ...[
              const SizedBox(height: 4),
              Center(
                child: Text(
                  email,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                  textDirection: TextDirection.ltr,
                ),
              ),
            ],
            const SizedBox(height: 32),
            _AccountTile(
              icon: Icons.person_outline,
              title: 'إعدادات الحساب',
              subtitle: 'الملف الشخصي، كلمة المرور، الإشعارات',
              onTap: () => _push(const AccountSettingsScreen()),
            ),
            if (_isInstructor)
              _AccountTile(
                icon: Icons.account_balance_wallet_outlined,
                title: 'الإدارة المالية',
                subtitle: 'الأرباح، الرصيد، أرباح الدورات',
                onTap: () => _push(const InstructorFinancesScreen()),
              ),
            _AccountTile(
              icon: Icons.card_membership_outlined,
              title: 'الاشتراكات',
              subtitle: 'الخطط واشتراكاتي',
              onTap: () => _push(const SubscriptionsScreen()),
            ),
            _AccountTile(
              icon: Icons.receipt_long_outlined,
              title: 'سجل المشتريات',
              subtitle: 'طلبات الدورات والمنتجات',
              onTap: () => _push(const PurchaseHistoryScreen()),
            ),
            _AccountTile(
              icon: Icons.help_outline,
              title: 'المساعدة والدعم',
              subtitle: 'تواصل معنا',
              onTap: () => _push(const SupportScreen()),
            ),
            _AccountTile(
              icon: Icons.notifications_none_rounded,
              title: 'الإشعارات والتنبيهات',
              subtitle: 'إشعارات، تنبيهات اختبارات ودروس',
              onTap: () => _push(const NotificationsRemindersScreen()),
            ),
            const Divider(height: 32),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: Colors.red),
              title: Text(
                'تسجيل الخروج',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: Colors.red.shade700,
                ),
              ),
              onTap: () async {
                await AuthApi.logout();
                if (!context.mounted) return;
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (route) => false,
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _avatarPlaceholder() {
    return Container(
      width: 96,
      height: 96,
      decoration: BoxDecoration(
        color: _primary.withValues(alpha: 0.2),
        shape: BoxShape.circle,
      ),
      child: Icon(Icons.person_rounded, size: 56, color: _primary),
    );
  }
}

class _AccountTile extends StatelessWidget {
  const _AccountTile({
    required this.icon,
    required this.title,
    this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String? subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: _primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: _primary, size: 24),
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
                              color: _primary,
                            ),
                        textDirection: TextDirection.rtl,
                      ),
                      if (subtitle != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          subtitle!,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                color: Colors.grey.shade600,
                              ),
                          textDirection: TextDirection.rtl,
                        ),
                      ],
                    ],
                  ),
                ),
                Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey.shade400),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

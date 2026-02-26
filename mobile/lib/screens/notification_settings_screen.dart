import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/notifications_api.dart';
import '../api/auth_api.dart';
import 'feature_scaffold.dart';
import 'notifications_screen.dart';

/// إعدادات الإشعارات — مرتبطة بمزايا الـ backend: عرض، تحديد كمقروء، حذف
class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  State<NotificationSettingsScreen> createState() => _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends State<NotificationSettingsScreen> {
  int _unreadCount = 0;
  bool _loading = true;
  bool _needsAuth = false;
  bool _markingAll = false;
  bool _deletingRead = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await NotificationsApi.getNotifications(page: 1, perPage: 1);
    final count = await NotificationsApi.getUnreadCount();
    if (!mounted) return;
    setState(() {
      _needsAuth = res.needsAuth;
      _unreadCount = count;
      _loading = false;
    });
  }

  Future<void> _markAllAsRead() async {
    if (_unreadCount == 0) return;
    setState(() => _markingAll = true);
    await NotificationsApi.markAllAsRead();
    if (!mounted) return;
    setState(() {
      _markingAll = false;
      _unreadCount = 0;
    });
  }

  Future<void> _deleteRead() async {
    setState(() => _deletingRead = true);
    await NotificationsApi.deleteRead();
    if (!mounted) return;
    setState(() => _deletingRead = false);
  }

  void _openNotificationsList() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const NotificationsScreen()),
    ).then((_) => _load());
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'إعدادات الإشعارات',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child: CircularProgressIndicator(color: AppTheme.primary),
                ),
              )
            : _needsAuth && AuthApi.token == null
                ? _NeedsAuth(onRefresh: _load)
                : SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        _SummaryCard(
                          unreadCount: _unreadCount,
                          onViewAll: _openNotificationsList,
                        ),
                        const SizedBox(height: 20),
                        if (_unreadCount > 0)
                          _ActionTile(
                            icon: Icons.done_all_rounded,
                            title: 'تحديد الكل كمقروء',
                            subtitle: 'تعليم جميع الإشعارات كمقروءة',
                            loading: _markingAll,
                            onTap: _markAllAsRead,
                          ),
                        _ActionTile(
                          icon: Icons.delete_sweep_rounded,
                          title: 'حذف الإشعارات المقروءة',
                          subtitle: 'حذف جميع الإشعارات المقروءة',
                          loading: _deletingRead,
                          onTap: _deleteRead,
                        ),
                        const SizedBox(height: 16),
                        OutlinedButton.icon(
                          onPressed: _openNotificationsList,
                          icon: const Icon(Icons.notifications_rounded, size: 22),
                          label: const Text('عرض جميع الإشعارات'),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            foregroundColor: AppTheme.primary,
                            side: const BorderSide(color: AppTheme.primary),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({
    required this.unreadCount,
    required this.onViewAll,
  });

  final int unreadCount;
  final VoidCallback onViewAll;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.primary.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Icon(
            Icons.notifications_active_rounded,
            size: 48,
            color: AppTheme.primary.withValues(alpha: 0.8),
          ),
          const SizedBox(height: 12),
          Text(
            unreadCount > 0 ? '$unreadCount إشعار غير مقروء' : 'لا توجد إشعارات غير مقروءة',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          if (unreadCount > 0) ...[
            const SizedBox(height: 8),
            TextButton(
              onPressed: onViewAll,
              child: const Text('عرض الإشعارات'),
            ),
          ],
        ],
      ),
    );
  }
}

class _ActionTile extends StatelessWidget {
  const _ActionTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.loading,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final bool loading;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 0,
        child: InkWell(
          onTap: loading ? null : onTap,
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
                if (loading)
                  const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primary),
                  )
                else
                  Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey.shade400),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _NeedsAuth extends StatelessWidget {
  const _NeedsAuth({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.login_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 20),
            Text(
              'سجّل الدخول لعرض إعدادات الإشعارات',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            TextButton.icon(
              onPressed: onRefresh,
              icon: const Icon(Icons.refresh_rounded, size: 20),
              label: const Text('تحديث'),
            ),
          ],
        ),
      ),
    );
  }
}

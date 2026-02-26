import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/auth_api.dart';
import '../api/notifications_api.dart';
import 'feature_scaffold.dart';
import 'login_screen.dart';

/// الإشعارات — مربوط بـ api/notifications مع حركات وقائمة تفاعلية
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key, this.embedded = false});

  /// عند true: يُعرض داخل تبويبات (بدون scaffold خاص)
  final bool embedded;

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _loading = true;
  int _page = 1;
  int _unreadCount = 0;
  List<AppNotification> _list = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  bool _needsAuth = false;

  Future<void> _load({bool refresh = false}) async {
    if (refresh) _page = 1;
    if (!refresh) {
      setState(() => _loading = true);
    }
    final res = await NotificationsApi.getNotifications(page: _page, perPage: 15);
    if (!mounted) return;
    setState(() {
      if (refresh) {
        _list = res.notifications;
      } else {
        _list = [..._list, ...res.notifications];
      }
      _unreadCount = res.unreadCount;
      _needsAuth = res.needsAuth;
      _loading = false;
    });
  }

  Future<void> _markAsRead(AppNotification n) async {
    if (n.isRead) return;
    await NotificationsApi.markAsRead(n.id);
    _load(refresh: true);
  }

  Future<void> _markAllRead() async {
    await NotificationsApi.markAllAsRead();
    _load(refresh: true);
  }

  Future<void> _deleteRead() async {
    await NotificationsApi.deleteRead();
    _load(refresh: true);
  }

  Future<void> _deleteOne(AppNotification n) async {
    final ok = await NotificationsApi.delete(n.id);
    if (ok && mounted) _load(refresh: true);
  }

  Widget _buildBody() {
    return RefreshIndicator(
        onRefresh: () => _load(refresh: true),
        color: AppTheme.primary,
        child: _loading && _list.isEmpty
            ? const Padding(
                padding: EdgeInsets.all(48),
                child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
              )
            : _needsAuth
                ? (AuthApi.token != null
                    ? _SessionExpiredNotifications(onRefresh: () => _load(refresh: true))
                    : _NeedsAuthNotifications())
                : _list.isEmpty
                    ? _EmptyNotifications(onRefresh: () => _load(refresh: true))
                    : Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_unreadCount > 0)
                          TweenAnimationBuilder<double>(
                            tween: AppTheme.fadeInTween(),
                            duration: AppTheme.animFast,
                            builder: (context, value, _) => Opacity(
                              opacity: value,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  '$_unreadCount غير مقروءة',
                                  style: Theme.of(context).textTheme.labelLarge?.copyWith(
                                        color: AppTheme.primary,
                                        fontWeight: FontWeight.bold,
                                      ),
                                ),
                              ),
                            ),
                          ),
                        if (_unreadCount > 0) const SizedBox(height: 16),
                        ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _list.length,
                          itemBuilder: (context, index) {
                            final n = _list[index];
                            return _NotificationTile(
                              index: index,
                              notification: n,
                              onTap: () => _markAsRead(n),
                              onDelete: () => _deleteOne(n),
                            );
                          },
                        ),
                        if (_loading && _list.isNotEmpty)
                          const Padding(
                            padding: EdgeInsets.all(16),
                            child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                          ),
                      ],
                    ),
                  ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (widget.embedded) {
      return _buildBody();
    }
    return FeatureScaffold(
      title: 'الإشعارات',
      actions: [
        if (!_needsAuth && _unreadCount > 0)
          TextButton(
            onPressed: _markAllRead,
            child: const Text('تحديد الكل كمقروء'),
          ),
        if (!_needsAuth && _list.any((e) => e.isRead))
          TextButton(
            onPressed: _deleteRead,
            child: const Text('حذف المقروء'),
          ),
      ],
      body: _buildBody(),
    );
  }
}

class _NotificationTile extends StatelessWidget {
  const _NotificationTile({
    required this.index,
    required this.notification,
    required this.onTap,
    this.onDelete,
  });

  final int index;
  final AppNotification notification;
  final VoidCallback onTap;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final isUnread = !notification.isRead;
    return TweenAnimationBuilder<double>(
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 260 + (index * 40)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.translate(
          offset: Offset(0, 16 * (1 - value)),
          child: Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Material(
              color: isUnread ? AppTheme.primary.withValues(alpha: 0.06) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 0,
              child: InkWell(
                onTap: onTap,
                borderRadius: BorderRadius.circular(16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withValues(alpha: 0.12),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          Icons.notifications_rounded,
                          color: AppTheme.primary,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              notification.title,
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: isUnread ? FontWeight.bold : FontWeight.w600,
                                    color: AppTheme.primaryDark,
                                  ),
                            ),
                            if (notification.body.isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(
                                notification.body,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                      color: Colors.grey.shade600,
                                    ),
                              ),
                            ],
                            const SizedBox(height: 6),
                            Text(
                              notification.createdAt,
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: Colors.grey.shade500,
                                  ),
                            ),
                          ],
                        ),
                      ),
                      if (onDelete != null)
                        IconButton(
                          icon: Icon(Icons.delete_outline_rounded, size: 22, color: Colors.grey.shade600),
                          onPressed: onDelete,
                          tooltip: 'حذف',
                        ),
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

class _NeedsAuthNotifications extends StatelessWidget {
  const _NeedsAuthNotifications();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.login_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 20),
          Text(
            'سجّل الدخول لعرض الإشعارات',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'يجب تسجيل الدخول لاستلام وعرض الإشعارات',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}

class _SessionExpiredNotifications extends StatelessWidget {
  const _SessionExpiredNotifications({required this.onRefresh});

  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.cloud_off_rounded, size: 72, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 20),
          Text(
            'تعذر تحميل الإشعارات',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'تحقق من الاتصال بالإنترنت وجرب التحديث',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              TextButton.icon(
                onPressed: () async {
                  await AuthApi.logout();
                  if (context.mounted) {
                    Navigator.of(context).pushAndRemoveUntil(
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                      (_) => false,
                    );
                  }
                },
                icon: const Icon(Icons.logout_rounded, size: 20),
                label: const Text('تسجيل الخروج'),
              ),
              const SizedBox(width: 12),
              TextButton.icon(
                onPressed: onRefresh,
                icon: const Icon(Icons.refresh_rounded, size: 20),
                label: const Text('تحديث'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _EmptyNotifications extends StatelessWidget {
  const _EmptyNotifications({required this.onRefresh});

  final VoidCallback onRefresh;

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
                Icon(Icons.notifications_off_rounded, size: 72, color: Colors.grey.shade400),
                const SizedBox(height: 16),
                Text(
                  'لا توجد إشعارات',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                ),
                const SizedBox(height: 8),
                TextButton.icon(
                  onPressed: onRefresh,
                  icon: const Icon(Icons.refresh_rounded, size: 20),
                  label: const Text('تحديث'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

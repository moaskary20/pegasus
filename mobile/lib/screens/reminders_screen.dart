import 'package:flutter/material.dart';
import '../api/auth_api.dart';
import '../api/reminders_api.dart';
import '../app_theme.dart';
import 'course_detail_screen.dart';
import 'feature_scaffold.dart';
import 'login_screen.dart';
import 'messages_screen.dart';
import 'my_courses_screen.dart';
import 'quiz_screen.dart';

/// شاشة التنبيهات — اختبارات، رسائل، دروس، كوبونات، إلخ
class RemindersScreen extends StatefulWidget {
  const RemindersScreen({super.key, this.embedded = false});

  /// عند true: يُعرض داخل تبويبات (بدون scaffold خاص)
  final bool embedded;

  @override
  State<RemindersScreen> createState() => _RemindersScreenState();
}

class _RemindersScreenState extends State<RemindersScreen> {
  bool _loading = true;
  List<ReminderItem> _reminders = [];
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await RemindersApi.getReminders();
    if (!mounted) return;
    setState(() {
      _reminders = res.reminders;
      _needsAuth = res.needsAuth;
      _loading = false;
    });
  }

  Future<void> _dismiss(ReminderItem item) async {
    final ok = await RemindersApi.dismiss(type: item.type, remindableId: item.remindableId);
    if (ok && mounted) await _load();
  }

  void _onReminderTap(ReminderItem item) {
    switch (item.type) {
      case 'quiz':
        if (item.courseSlug != null && item.courseSlug!.isNotEmpty && item.lessonId != null) {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => QuizScreen(
                courseSlug: item.courseSlug!,
                courseTitle: item.title,
                lessonId: item.lessonId!,
              ),
            ),
          ).then((_) => _load());
        }
        break;
      case 'message':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MessagesScreen()),
        ).then((_) => _load());
        break;
      case 'lesson':
      case 'rating':
        if (item.courseSlug != null && item.courseSlug!.isNotEmpty) {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => CourseDetailScreen(courseSlug: item.courseSlug!, courseTitle: item.title),
            ),
          ).then((_) => _load());
        }
        break;
      case 'certificate':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyCoursesScreen()),
        ).then((_) => _load());
        break;
      case 'coupon':
      case 'question':
        // للإدارة/المدربين — لا تنقل ضمن التطبيق
        break;
      default:
        break;
    }
  }

  Widget _buildBody() {
    return RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _needsAuth
                ? _buildNeedsAuth()
                : _reminders.isEmpty
                    ? _buildEmpty()
                    : ListView.builder(
                        padding: const EdgeInsets.all(20),
                        itemCount: _reminders.length,
                        itemBuilder: (_, i) => _ReminderTile(
                          item: _reminders[i],
                          onDismiss: () => _dismiss(_reminders[i]),
                          onTap: () => _onReminderTap(_reminders[i]),
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
      title: 'التنبيهات',
      body: _buildBody(),
    );
  }

  Widget _buildNeedsAuth() {
    // إذا وُجد توكن في الذاكرة ولكن الـ API أرجع 401 → انتهت الجلسة أو مشكلة اتصال
    final hasToken = AuthApi.token != null;
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              hasToken ? Icons.cloud_off_rounded : Icons.lock_outline_rounded,
              size: 64,
              color: Colors.grey.shade400,
            ),
            const SizedBox(height: 16),
            Text(
              hasToken
                  ? 'تعذر تحميل التنبيهات'
                  : 'يجب تسجيل الدخول لعرض التنبيهات',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
              textDirection: TextDirection.rtl,
            ),
            if (hasToken) ...[
              const SizedBox(height: 8),
              Text(
                'تحقق من الاتصال وجرّب التحديث، أو سجّل الدخول مجدداً',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
                textAlign: TextAlign.center,
                textDirection: TextDirection.rtl,
              ),
            ],
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (hasToken) ...[
                  TextButton.icon(
                    onPressed: () async {
                      await AuthApi.logout();
                      if (mounted) {
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
                  FilledButton.icon(
                    onPressed: () => _load(),
                    icon: const Icon(Icons.refresh_rounded, size: 20),
                    label: const Text('تحديث'),
                    style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                  ),
                ] else ...[
                  TextButton.icon(
                    onPressed: () => _load(),
                    icon: const Icon(Icons.refresh_rounded, size: 20),
                    label: const Text('تحديث'),
                  ),
                  const SizedBox(width: 12),
                  FilledButton(
                    onPressed: () => Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                    ),
                    style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                    child: const Text('تسجيل الدخول'),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.check_circle_outline_rounded, size: 80, color: Colors.green.shade300),
          const SizedBox(height: 16),
          Text(
            'لا توجد تنبيهات حالياً',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
            textDirection: TextDirection.rtl,
          ),
        ],
      ),
    );
  }
}

class _ReminderTile extends StatelessWidget {
  const _ReminderTile({required this.item, required this.onDismiss, required this.onTap});

  final ReminderItem item;
  final VoidCallback onDismiss;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 1,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              textDirection: TextDirection.rtl,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _iconForType(item.type),
                    color: AppTheme.primary,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.title,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primaryDark,
                            ),
                        textDirection: TextDirection.rtl,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item.message,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                              color: Colors.grey.shade600,
                            ),
                        textDirection: TextDirection.rtl,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      if (item.actionLabel != null && item.actionLabel!.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text(
                          item.actionLabel!,
                          style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.primary,
                            fontWeight: FontWeight.w600,
                          ),
                          textDirection: TextDirection.rtl,
                        ),
                      ],
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, size: 20),
                  onPressed: onDismiss,
                  tooltip: 'إخفاء',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  IconData _iconForType(String type) {
    switch (type) {
      case 'quiz': return Icons.quiz_outlined;
      case 'message': return Icons.chat_bubble_outline_rounded;
      case 'lesson': return Icons.play_circle_outline_rounded;
      case 'coupon': return Icons.local_offer_outlined;
      case 'certificate': return Icons.workspace_premium_outlined;
      case 'rating': return Icons.star_outline_rounded;
      case 'question': return Icons.help_outline_rounded;
      default: return Icons.notifications_outlined;
    }
  }
}

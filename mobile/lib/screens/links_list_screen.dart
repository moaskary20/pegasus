import 'package:flutter/material.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';
import 'my_courses_screen.dart';
import 'my_assignments_screen.dart';
import 'cart_screen.dart';
import 'wishlist_screen.dart';
import 'notifications_screen.dart';
import 'messages_screen.dart';
import 'account_settings_screen.dart';
import 'subscriptions_screen.dart';
import 'purchase_history_screen.dart';
import 'support_screen.dart';

/// قائمة الروابط: شبكة روابط سريعة لجميع الأقسام مع حركات
class LinksListScreen extends StatelessWidget {
  const LinksListScreen({super.key});

  static final List<_LinkItem> _items = [
    _LinkItem(Icons.school_rounded, 'تعلّمي / دوراتي', const Color(0xFF6366F1), () => MyCoursesScreen()),
    _LinkItem(Icons.assignment_outlined, 'واجباتي', const Color(0xFF8B5CF6), () => MyAssignmentsScreen()),
    _LinkItem(Icons.shopping_cart_outlined, 'سلة المشتريات', const Color(0xFFA78BFA), () => CartScreen()),
    _LinkItem(Icons.favorite_border_rounded, 'قائمة الرغبات', const Color(0xFFEC4899), () => WishlistScreen()),
    _LinkItem(Icons.notifications_none_rounded, 'الإشعارات', const Color(0xFFF59E0B), () => NotificationsScreen()),
    _LinkItem(Icons.chat_bubble_outline_rounded, 'الرسائل', const Color(0xFF10B981), () => MessagesScreen()),
    _LinkItem(Icons.settings_outlined, 'إعدادات الحساب', const Color(0xFF64748B), () => AccountSettingsScreen()),
    _LinkItem(Icons.card_membership_outlined, 'الاشتراكات', const Color(0xFF0EA5E9), () => SubscriptionsScreen()),
    _LinkItem(Icons.receipt_long_outlined, 'سجل المشتريات', const Color(0xFF14B8A6), () => PurchaseHistoryScreen()),
    _LinkItem(Icons.help_outline_rounded, 'المساعدة والدعم', const Color(0xFFF97316), () => SupportScreen()),
  ];

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'قائمة الروابط',
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            TweenAnimationBuilder<double>(
              tween: AppTheme.fadeInTween(),
              duration: AppTheme.animNormal,
              builder: (context, value, child) => Opacity(
                opacity: value,
                child: Text(
                  'اختر القسم المطلوب',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
                ),
              ),
            ),
            const SizedBox(height: 20),
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.05,
              ),
              itemCount: _items.length,
              itemBuilder: (context, index) {
                final item = _items[index];
                return TweenAnimationBuilder<double>(
                  tween: AppTheme.scaleInTween(),
                  duration: Duration(milliseconds: 300 + (index * 35)),
                  curve: AppTheme.curveEmphasized,
                  builder: (context, value, _) => Transform.scale(
                    scale: value,
                    child: Material(
                      color: item.color.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                      child: InkWell(
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => item.screen()),
                          );
                        },
                        borderRadius: BorderRadius.circular(20),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: item.color.withValues(alpha: 0.2),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(item.icon, color: item.color, size: 28),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                item.label,
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.primaryDark,
                                    ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _LinkItem {
  _LinkItem(this.icon, this.label, this.color, this.screen);
  final IconData icon;
  final String label;
  final Color color;
  final Widget Function() screen;
}

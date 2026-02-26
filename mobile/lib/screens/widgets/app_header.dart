import 'package:flutter/material.dart';

const Color _primary = Color(0xFF2c004d);

/// هيدر موحد: سلة، جرس، مفضلة | اسم التطبيق/العنوان | رسائل، قائمة جانبية
class AppHeader extends StatelessWidget implements PreferredSizeWidget {
  const AppHeader({
    super.key,
    this.title,
    this.onCart,
    this.cartCount = 0,
    this.onBell,
    this.notificationsCount = 0,
    this.onReminders,
    this.remindersCount = 0,
    this.onFavorite,
    this.favoriteCount = 0,
    this.onMessages,
    this.messagesCount = 0,
    this.onMenu,
  });

  /// النص في المنتصف (افتراضي: أكاديمية بيغاسوس)
  final String? title;
  final VoidCallback? onCart;
  /// عدد عناصر السلة (يُظهر بالأحمر فوق الأيقونة)
  final int cartCount;
  final VoidCallback? onBell;
  /// عدد الإشعارات غير المقروءة (يُظهر بالأحمر فوق الأيقونة)
  final int notificationsCount;
  final VoidCallback? onReminders;
  /// عدد التنبيهات (اختبارات، دروس، إلخ)
  final int remindersCount;
  final VoidCallback? onFavorite;
  /// عدد عناصر المفضلة (يُظهر فوق القلب ويُلوّن القلب بالأحمر عند > 0)
  final int favoriteCount;
  final VoidCallback? onMessages;
  /// عدد الرسائل غير المقروءة (يُظهر بالأحمر فوق الأيقونة)
  final int messagesCount;
  final VoidCallback? onMenu;

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: _primary,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: true,
      title: Text(
        title ?? 'أكاديمية بيغاسوس',
        style: const TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
      ),
      leading: Padding(
        padding: const EdgeInsets.only(right: 0),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            IconButton(
              icon: const Icon(Icons.menu_rounded),
              onPressed: onMenu ?? () {},
              tooltip: 'القائمة',
            ),
            _IconWithBadge(
              icon: const Icon(Icons.notifications_active_outlined),
              count: remindersCount,
              onPressed: onReminders ?? () {},
              tooltip: 'التنبيهات',
            ),
            _IconWithBadge(
              icon: const Icon(Icons.chat_bubble_outline_rounded),
              count: messagesCount,
              onPressed: onMessages ?? () {},
              tooltip: 'الرسائل',
            ),
          ],
        ),
      ),
      leadingWidth: 130,
      actions: [
        Stack(
          clipBehavior: Clip.none,
          children: [
            IconButton(
              icon: Icon(
                favoriteCount > 0 ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                color: favoriteCount > 0 ? Colors.red : Colors.white,
              ),
              onPressed: onFavorite ?? () {},
              tooltip: 'المفضلة',
            ),
            if (favoriteCount > 0)
              Positioned(
                top: 4,
                left: 4,
                child: _Badge(count: favoriteCount),
              ),
          ],
        ),
        _IconWithBadge(
          icon: const Icon(Icons.notifications_none_rounded),
          count: notificationsCount,
          onPressed: onBell ?? () {},
          tooltip: 'الإشعارات',
        ),
        _IconWithBadge(
          icon: const Icon(Icons.shopping_cart_outlined),
          count: cartCount,
          onPressed: onCart ?? () {},
          tooltip: 'السلة',
        ),
      ],
    );
  }
}

class _Badge extends StatelessWidget {
  const _Badge({required this.count});

  final int count;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
      constraints: const BoxConstraints(minWidth: 18),
      decoration: BoxDecoration(
        color: Colors.red,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: _primary, width: 1),
      ),
      child: Text(
        count > 99 ? '99+' : '$count',
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _IconWithBadge extends StatelessWidget {
  const _IconWithBadge({
    required this.icon,
    required this.count,
    required this.onPressed,
    required this.tooltip,
  });

  final Widget icon;
  final int count;
  final VoidCallback onPressed;
  final String tooltip;

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        IconButton(
          icon: icon,
          onPressed: onPressed,
          tooltip: tooltip,
        ),
        if (count > 0)
          Positioned(
            top: 4,
            left: 4,
            child: _Badge(count: count),
          ),
      ],
    );
  }
}

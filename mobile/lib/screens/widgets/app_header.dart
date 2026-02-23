import 'package:flutter/material.dart';

const Color _primary = Color(0xFF2c004d);

/// هيدر موحد: سلة، جرس، مفضلة | اسم التطبيق/العنوان | رسائل، قائمة جانبية
class AppHeader extends StatelessWidget implements PreferredSizeWidget {
  const AppHeader({
    super.key,
    this.title,
    this.onCart,
    this.onBell,
    this.onFavorite,
    this.onMessages,
    this.onMenu,
  });

  /// النص في المنتصف (افتراضي: أكاديمية بيغاسوس)
  final String? title;
  final VoidCallback? onCart;
  final VoidCallback? onBell;
  final VoidCallback? onFavorite;
  final VoidCallback? onMessages;
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
            IconButton(
              icon: const Icon(Icons.chat_bubble_outline_rounded),
              onPressed: onMessages ?? () {},
              tooltip: 'الرسائل',
            ),
          ],
        ),
      ),
      leadingWidth: 100,
      actions: [
        IconButton(
          icon: const Icon(Icons.favorite_border_rounded),
          onPressed: onFavorite ?? () {},
          tooltip: 'المفضلة',
        ),
        IconButton(
          icon: const Icon(Icons.notifications_none_rounded),
          onPressed: onBell ?? () {},
          tooltip: 'الإشعارات',
        ),
        IconButton(
          icon: const Icon(Icons.shopping_cart_outlined),
          onPressed: onCart ?? () {},
          tooltip: 'السلة',
        ),
      ],
    );
  }
}

import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:rive_animated_icon/rive_animated_icon.dart';
import 'tabs/home_tab.dart';
import 'tabs/courses_tab.dart';
import 'tabs/store_tab.dart';
import 'tabs/account_tab.dart';
import 'widgets/app_drawer.dart';
import 'wishlist_screen.dart';
import 'cart_screen.dart';
import 'notifications_screen.dart';
import '../api/wishlist_api.dart';

const Color _primary = Color(0xFF2c004d);

enum NavItem {
  home(0, 'الرئيسية', RiveIcon.home),
  courses(1, 'الدورات', RiveIcon.graduate),
  store(2, 'الاستور', null),
  account(3, 'حسابي', RiveIcon.profile);

  const NavItem(this.tabIndex, this.label, this.riveIcon);
  final int tabIndex;
  final String label;
  final RiveIcon? riveIcon;
}

/// الهيكل الرئيسي مع شريط تنقل: عند التحديد تظهر دائرة بارزة للأعلى والأيقونة داخلها
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> with SingleTickerProviderStateMixin {
  int _currentIndex = 0;
  int _wishlistCount = 0;
  final _pageController = PageController();
  final _scaffoldKey = GlobalKey<ScaffoldState>();
  bool _drawerVisible = false;
  late final AnimationController _drawerController;
  late final Animation<double> _drawerAnimation;

  @override
  void initState() {
    super.initState();
    _drawerController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
    );
    _drawerAnimation = CurvedAnimation(
      parent: _drawerController,
      curve: Curves.easeOutCubic,
    );
    _loadWishlistCount();
  }

  Future<void> _loadWishlistCount() async {
    final res = await WishlistApi.getWishlist();
    if (mounted) {
      setState(() => _wishlistCount = res.courses.length + res.products.length);
    }
  }

  void _openWishlist() {
    Navigator.of(context)
        .push(MaterialPageRoute(builder: (_) => const WishlistScreen()))
        .then((_) => _loadWishlistCount());
  }

  void _onWishlistCountChanged(int delta) {
    setState(() => _wishlistCount = (_wishlistCount + delta).clamp(0, 999));
  }

  @override
  void dispose() {
    _drawerController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  void _onTap(int index) {
    if (_currentIndex == index) return;
    setState(() => _currentIndex = index);
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
    );
  }

  void openDrawer() {
    if (_drawerVisible) return;
    setState(() => _drawerVisible = true);
    _drawerController.forward();
  }

  void _closeDrawer() {
    _drawerController.reverse().then((_) {
      if (mounted) setState(() => _drawerVisible = false);
    });
  }

  @override
  Widget build(BuildContext context) {
    final isRTL = Directionality.of(context) == TextDirection.rtl;
    return Scaffold(
      key: _scaffoldKey,
      body: Stack(
        children: [
          PageView(
            controller: _pageController,
            physics: const NeverScrollableScrollPhysics(),
            children: [
              HomeTab(
                onOpenDrawer: openDrawer,
                wishlistCount: _wishlistCount,
                onWishlistCountChanged: _onWishlistCountChanged,
                onOpenFavorite: _openWishlist,
                onOpenCart: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                ),
                onOpenNotifications: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
              ),
              CoursesTab(
                onOpenDrawer: openDrawer,
                wishlistCount: _wishlistCount,
                onWishlistCountChanged: _onWishlistCountChanged,
                onOpenFavorite: _openWishlist,
                onOpenCart: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                ),
                onOpenNotifications: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
              ),
              StoreTab(
                onOpenDrawer: openDrawer,
                wishlistCount: _wishlistCount,
                onWishlistCountChanged: _onWishlistCountChanged,
                onOpenFavorite: _openWishlist,
                onOpenCart: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                ),
                onOpenNotifications: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
              ),
              AccountTab(
                onOpenDrawer: openDrawer,
                wishlistCount: _wishlistCount,
                onWishlistCountChanged: _onWishlistCountChanged,
                onOpenFavorite: _openWishlist,
                onOpenCart: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CartScreen()),
                ),
                onOpenNotifications: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ),
              ),
            ],
          ),
          if (_drawerVisible)
            AnimatedBuilder(
              animation: _drawerAnimation,
              builder: (context, _) {
                return _Drawer3DOverlay(
                  value: _drawerAnimation.value,
                  isRTL: isRTL,
                  onClose: _closeDrawer,
                  child: AppDrawerContent(onClose: _closeDrawer),
                );
              },
            ),
        ],
      ),
      bottomNavigationBar: _FloatingCircleNavBar(
        currentIndex: _currentIndex,
        onTap: _onTap,
      ),
    );
  }
}

class _Drawer3DOverlay extends StatelessWidget {
  const _Drawer3DOverlay({
    required this.value,
    required this.isRTL,
    required this.onClose,
    required this.child,
  });

  final double value;
  final bool isRTL;
  final VoidCallback onClose;
  final Widget child;

  static const double _drawerWidth = 300.0;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        GestureDetector(
          onTap: onClose,
          child: Container(
            color: Colors.black.withValues(alpha: 0.4 * value),
          ),
        ),
        Align(
          alignment: isRTL ? Alignment.centerRight : Alignment.centerLeft,
          child: Transform(
            alignment: isRTL ? Alignment.centerRight : Alignment.centerLeft,
            transform: Matrix4.identity()
              ..setEntry(3, 2, 0.001)
              ..rotateY((isRTL ? 1 : -1) * (1 - value) * math.pi / 2),
            child: Container(
              width: _drawerWidth,
              height: double.infinity,
              decoration: BoxDecoration(
                color: Theme.of(context).drawerTheme.backgroundColor ?? Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.2),
                    blurRadius: 12,
                    offset: Offset((isRTL ? 1 : -1) * 4, 0),
                  ),
                ],
              ),
              child: child,
            ),
          ),
        ),
      ],
    );
  }
}

class _FloatingCircleNavBar extends StatelessWidget {
  const _FloatingCircleNavBar({
    required this.currentIndex,
    required this.onTap,
  });

  final int currentIndex;
  final ValueChanged<int> onTap;

  static const double _barHeight = 56;
  static const double _circleDiameter = 52;
  static const double _circleRadius = _circleDiameter / 2;
  static const int _itemCount = 4;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: _barHeight + _circleRadius + 8,
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).padding.bottom),
      child: Stack(
        clipBehavior: Clip.none,
        alignment: Alignment.bottomCenter,
        children: [
          // الشريط الأبيض المستدير من الأعلى
          Positioned(
            left: 16,
            right: 16,
            bottom: 0,
            child: Container(
              height: _barHeight,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 16,
                    offset: const Offset(0, -4),
                  ),
                ],
              ),
            ),
          ),
          // الدائرة العائمة + الأيقونات والنصوص
          LayoutBuilder(
            builder: (context, constraints) {
              final barWidth = constraints.maxWidth - 32;
              final itemWidth = barWidth / _itemCount;
              final circleCenterX = 16 + itemWidth * (3 - currentIndex) + itemWidth / 2;

              return Stack(
                clipBehavior: Clip.none,
                children: [
                  // الدائرة للعنصر المحدد (بارزة للأعلى)
                  AnimatedPositioned(
                    duration: const Duration(milliseconds: 280),
                    curve: Curves.easeInOut,
                    left: circleCenterX - _circleRadius,
                    bottom: _barHeight - _circleRadius - 4,
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () => onTap(currentIndex),
                        customBorder: const CircleBorder(),
                        child: Container(
                          width: _circleDiameter,
                          height: _circleDiameter,
                          decoration: BoxDecoration(
                            color: _primary,
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: _primary.withValues(alpha: 0.35),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Center(
                            child: NavItem.values[currentIndex].riveIcon != null
                                ? RiveAnimatedIcon(
                                    key: ValueKey('nav_rive_sel_$currentIndex'),
                                    riveIcon: NavItem.values[currentIndex].riveIcon!,
                                    width: 28,
                                    height: 28,
                                    color: Colors.white,
                                    strokeWidth: 2,
                                    loopAnimation: true,
                                    onTap: () => onTap(currentIndex),
                                  )
                                : Icon(Icons.store_rounded, size: 28, color: Colors.white),
                          ),
                        ),
                      ),
                    ),
                  ),
                  // كل العناصر: أيقونة + نص للمحدد فقط (كل عنصر بعرضه فقط لاستقبال الضغط بشكل صحيح)
                  ...List.generate(_itemCount, (index) {
                    final item = NavItem.values[index];
                    final selected = index == currentIndex;
                    final centerX = 16 + itemWidth * (3 - index) + itemWidth / 2;
                    final itemLeft = centerX - itemWidth / 2;

                    return Positioned(
                      left: itemLeft,
                      width: itemWidth,
                      bottom: 0,
                      height: _barHeight + _circleRadius + 8,
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: () => onTap(index),
                          borderRadius: BorderRadius.circular(24),
                          child: SizedBox(
                            width: itemWidth,
                            height: _barHeight + _circleRadius + 8,
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                if (selected) ...[
                                  const Spacer(),
                                  Text(
                                    item.label,
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      color: _primary,
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                ] else ...[
                                  const Spacer(),
                                  SizedBox(
                                    width: 28,
                                    height: 28,
                                    child: item.riveIcon != null
                                        ? RiveAnimatedIcon(
                                            key: ValueKey('nav_rive_$index'),
                                            riveIcon: item.riveIcon!,
                                            width: 28,
                                            height: 28,
                                            color: Colors.grey.shade500,
                                            strokeWidth: 2,
                                            loopAnimation: false,
                                            onTap: () => onTap(index),
                                          )
                                        : Icon(Icons.store_rounded, size: 28, color: Colors.grey.shade500),
                                  ),
                                  const SizedBox(height: 14),
                                ],
                              ],
                            ),
                          ),
                        ),
                      ),
                    );
                  }),
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}

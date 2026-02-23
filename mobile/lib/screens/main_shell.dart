import 'package:flutter/material.dart';
import 'package:rive_animated_icon/rive_animated_icon.dart';
import 'tabs/home_tab.dart';
import 'tabs/my_courses_tab.dart';
import 'tabs/search_tab.dart';
import 'tabs/account_tab.dart';
import 'widgets/app_drawer.dart';

const Color _primary = Color(0xFF2c004d);

enum NavItem {
  home(0, 'الرئيسية', RiveIcon.home),
  courses(1, 'دوراتى', RiveIcon.graduate),
  search(2, 'البحث', RiveIcon.search),
  account(3, 'حسابي', RiveIcon.profile);

  const NavItem(this.tabIndex, this.label, this.riveIcon);
  final int tabIndex;
  final String label;
  final RiveIcon riveIcon;
}

/// الهيكل الرئيسي مع شريط تنقل: عند التحديد تظهر دائرة بارزة للأعلى والأيقونة داخلها
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _currentIndex = 0;
  final _pageController = PageController();
  final _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void dispose() {
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
    _scaffoldKey.currentState?.openDrawer();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      drawer: const AppDrawer(),
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        children: [
          HomeTab(onOpenDrawer: openDrawer),
          MyCoursesTab(onOpenDrawer: openDrawer),
          SearchTab(onOpenDrawer: openDrawer),
          AccountTab(onOpenDrawer: openDrawer),
        ],
      ),
      bottomNavigationBar: _FloatingCircleNavBar(
        currentIndex: _currentIndex,
        onTap: _onTap,
      ),
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
                            child: RiveAnimatedIcon(
                              key: ValueKey('nav_rive_sel_$currentIndex'),
                              riveIcon: NavItem.values[currentIndex].riveIcon,
                              width: 28,
                              height: 28,
                              color: Colors.white,
                              strokeWidth: 2,
                              loopAnimation: true,
                              onTap: () => onTap(currentIndex),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  // كل العناصر: أيقونة + نص للمحدد فقط
                  ...List.generate(_itemCount, (index) {
                    final item = NavItem.values[index];
                    final selected = index == currentIndex;
                    final centerX = 16 + itemWidth * (3 - index) + itemWidth / 2;

                    return Positioned(
                      left: 0,
                      right: 0,
                      bottom: 0,
                      child: SizedBox(
                        height: _barHeight + _circleRadius + 8,
                        child: Stack(
                          clipBehavior: Clip.none,
                          children: [
                            // منطقة الضغط
                            Positioned(
                              left: centerX - itemWidth / 2,
                              width: itemWidth,
                              top: 0,
                              bottom: 0,
                              child: Material(
                                color: Colors.transparent,
                                child: InkWell(
                                  onTap: () => onTap(index),
                                  borderRadius: BorderRadius.circular(24),
                                  child: const SizedBox.expand(),
                                ),
                              ),
                            ),
                            // محتوى العنصر
                            Positioned(
                              left: centerX - itemWidth / 2,
                              width: itemWidth,
                              bottom: 0,
                              child: SizedBox(
                                height: _barHeight + _circleRadius,
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
                                        child: RiveAnimatedIcon(
                                          key: ValueKey('nav_rive_$index'),
                                          riveIcon: item.riveIcon,
                                          width: 28,
                                          height: 28,
                                          color: Colors.grey.shade500,
                                          strokeWidth: 2,
                                          loopAnimation: false,
                                          onTap: () => onTap(index),
                                        ),
                                      ),
                                      const SizedBox(height: 14),
                                    ],
                                  ],
                                ),
                              ),
                            ),
                          ],
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

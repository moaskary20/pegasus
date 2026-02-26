import 'package:flutter/material.dart';

/// انتقال انزلاقي مع تلاشي عند فتح الشاشات
class SlidePageRoute<T> extends PageRouteBuilder<T> {
  SlidePageRoute({
    required this.page,
    this.direction = AxisDirection.left,
    super.settings,
  }) : super(
          pageBuilder: (context, animation, secondaryAnimation) => page,
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            final begin = direction == AxisDirection.left
                ? const Offset(1, 0)
                : direction == AxisDirection.right
                    ? const Offset(-1, 0)
                    : direction == AxisDirection.up
                        ? const Offset(0, 1)
                        : const Offset(0, -1);
            final end = Offset.zero;
            final curved = CurvedAnimation(
              parent: animation,
              curve: Curves.easeOutCubic,
            );
            return SlideTransition(
              position: Tween<Offset>(begin: begin, end: end).animate(curved),
              child: FadeTransition(
                opacity: curved,
                child: child,
              ),
            );
          },
          transitionDuration: const Duration(milliseconds: 300),
        );

  final Widget page;
  final AxisDirection direction;
}

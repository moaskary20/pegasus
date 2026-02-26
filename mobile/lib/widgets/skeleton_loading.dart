import 'package:flutter/material.dart';

/// هيكل وهمي (skeleton) أثناء التحميل — يعطي انطباعاً بصرياً أفضل من CircularProgressIndicator
class SkeletonLoading extends StatefulWidget {
  const SkeletonLoading({
    super.key,
    required this.child,
    this.duration = const Duration(milliseconds: 1200),
  });

  /// شكل الهيكل الوهمي (عادةً Container بلون رمادي)
  final Widget child;
  final Duration duration;

  @override
  State<SkeletonLoading> createState() => _SkeletonLoadingState();
}

class _SkeletonLoadingState extends State<SkeletonLoading>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: widget.duration)
      ..repeat(reverse: true);
    _animation = Tween<double>(begin: 0.4, end: 0.8).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _animation,
      builder: (context, _) {
        return Opacity(opacity: _animation.value, child: widget.child);
      },
    );
  }
}

/// هيكل مستطيل بقيمة ارتفاع
class SkeletonBox extends StatelessWidget {
  const SkeletonBox({
    super.key,
    this.width,
    this.height = 16,
    this.borderRadius,
  });

  final double? width;
  final double height;
  final BorderRadius? borderRadius;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: Colors.grey.shade300,
        borderRadius: borderRadius ?? BorderRadius.circular(4),
      ),
    );
  }
}

/// هيكل بطاقة دورة (صورة + نص)
class SkeletonCourseCard extends StatelessWidget {
  const SkeletonCourseCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SkeletonLoading(
            child: Container(
              height: 120,
              width: double.infinity,
              color: Colors.grey,
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SkeletonLoading(child: SkeletonBox(height: 18, width: double.infinity)),
                const SizedBox(height: 8),
                SkeletonLoading(child: SkeletonBox(height: 14, width: 120)),
                const SizedBox(height: 8),
                SkeletonLoading(child: SkeletonBox(height: 14, width: 80)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// هيكل قائمة عناصر (للشاشات التي تعرض قوائم)
class SkeletonListTile extends StatelessWidget {
  const SkeletonListTile({
    super.key,
    this.hasLeading = true,
    this.hasSubtitle = true,
  });

  final bool hasLeading;
  final bool hasSubtitle;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          if (hasLeading)
            SkeletonLoading(
              child: Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          if (hasLeading) const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SkeletonLoading(child: SkeletonBox(height: 16, width: double.infinity)),
                if (hasSubtitle) ...[
                  const SizedBox(height: 6),
                  SkeletonLoading(child: SkeletonBox(height: 12, width: 160)),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// هيكل للشاشة الرئيسية (سلايدر + أقسام)
class SkeletonHomePage extends StatelessWidget {
  const SkeletonHomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SkeletonLoading(
            child: Container(
              height: 180,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          const SizedBox(height: 24),
          SkeletonLoading(child: SkeletonBox(height: 24, width: 140)),
          const SizedBox(height: 12),
          SizedBox(
            height: 200,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: 3,
              separatorBuilder: (_, __) => const SizedBox(width: 12),
              itemBuilder: (_, __) => const SizedBox(
                width: 160,
                child: SkeletonCourseCard(),
              ),
            ),
          ),
          const SizedBox(height: 24),
          SkeletonLoading(child: SkeletonBox(height: 24, width: 120)),
          const SizedBox(height: 12),
          ...List.generate(4, (_) => const SkeletonListTile()),
        ],
      ),
    );
  }
}

/// هيكل لشاشة تصنيفات الدورات
class SkeletonCategoriesPage extends StatelessWidget {
  const SkeletonCategoriesPage({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SkeletonLoading(
            child: Container(
              height: 120,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          const SizedBox(height: 24),
          SkeletonLoading(child: SkeletonBox(height: 20, width: 180)),
          const SizedBox(height: 16),
          ...List.generate(5, (_) => const SkeletonListTile(hasLeading: true, hasSubtitle: true)),
        ],
      ),
    );
  }
}

/// هيكل تفاصيل الدورة
class SkeletonCourseDetail extends StatelessWidget {
  const SkeletonCourseDetail({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SkeletonLoading(
            child: Container(
              height: 220,
              width: double.infinity,
              color: Colors.grey.shade300,
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SkeletonLoading(child: SkeletonBox(height: 24, width: double.infinity)),
                const SizedBox(height: 12),
                SkeletonLoading(child: SkeletonBox(height: 16, width: 100)),
                const SizedBox(height: 16),
                SkeletonLoading(child: SkeletonBox(height: 14, width: 200)),
                const SizedBox(height: 24),
                SkeletonLoading(child: SkeletonBox(height: 40, width: double.infinity)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

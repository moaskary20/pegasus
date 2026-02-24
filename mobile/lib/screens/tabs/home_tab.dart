import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../../api/home_api.dart';
import '../../api/config.dart';
import '../../app_theme.dart';

/// تبويب الرئيسية: دورات مميزة أفقياً + أحدث الدورات عمودياً (مطابق للتصميم المطلوب)
class HomeTab extends StatefulWidget {
  const HomeTab({super.key, this.onOpenDrawer});

  final VoidCallback? onOpenDrawer;

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  bool _loading = true;
  HomeResponse? _data;
  final Set<int> _wishlistIds = {};
  final PageController _topCoursesPageController = PageController(viewportFraction: 0.98);
  final PageController _recentCoursesPageController = PageController(viewportFraction: 0.98);
  final PageController _homeSliderPageController = PageController();
  int _homeSliderPage = 0;

  @override
  void dispose() {
    _topCoursesPageController.dispose();
    _recentCoursesPageController.dispose();
    _homeSliderPageController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await HomeApi.getHome();
    if (!mounted) return;
    setState(() {
      _data = res;
      _wishlistIds.clear();
      _wishlistIds.addAll(res.wishlistIds);
      _loading = false;
    });
  }

  void _toggleWishlist(int courseId) {
    setState(() {
      if (_wishlistIds.contains(courseId)) {
        _wishlistIds.remove(courseId);
      } else {
        _wishlistIds.add(courseId);
      }
    });
    // TODO: استدعاء API إضافة/إزالة من المفضلة عند توفرها
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F8F8),
      appBar: AppHeader(
        title: 'أكاديمية بيغاسوس',
        onMenu: widget.onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'مرحباً بك',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primaryDark,
                                ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'استكشف الدورات وابدأ التعلم',
                            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                  color: Colors.grey.shade600,
                                ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  if (_data != null) ...[
                    _buildHomeSlider(_data!.homeSlider),
                    _buildSectionTitle('الدورات الأكثر مشاهدة', '🔥'),
                    _buildTopCourses(_data!.topCourses),
                    _buildSectionTitle('أحدث الدورات', '⚡'),
                    _buildRecentCourses(_data!.recentCourses),
                    _buildCategoriesSection(_data!.categories),
                  ] else
                    const SliverFillRemaining(
                      hasScrollBody: false,
                      child: Center(child: Text('لا توجد بيانات')),
                    ),
                  const SliverToBoxAdapter(child: SizedBox(height: 24)),
                ],
              ),
      ),
    );
  }

  Widget _buildSectionTitle(String title, String emoji) {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 12),
        child: Row(
          children: [
            Text(
              emoji,
              style: const TextStyle(fontSize: 22),
            ),
            const SizedBox(width: 8),
            Text(
              title,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF333333),
                  ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHomeSlider(List<HomeSlideItem> slides) {
    if (slides.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
        child: SizedBox(
          height: 200,
          child: Stack(
            children: [
              PageView.builder(
                controller: _homeSliderPageController,
                onPageChanged: (i) => setState(() => _homeSliderPage = i),
                itemCount: slides.length,
                itemBuilder: (context, index) => _HomeSliderSlide(slide: slides[index]),
              ),
              if (slides.length > 1)
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 12,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(
                      slides.length,
                      (i) {
                        final active = i == _homeSliderPage;
                        return GestureDetector(
                          onTap: () => _homeSliderPageController.animateToPage(
                            i,
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeOutCubic,
                          ),
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            margin: const EdgeInsets.symmetric(horizontal: 3),
                            width: active ? 20 : 8,
                            height: 8,
                            decoration: BoxDecoration(
                              color: active ? Colors.white : Colors.white.withValues(alpha: 0.4),
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.5), width: 1),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTopCourses(List<CourseItem> list) {
    if (list.isEmpty) {
      return SliverToBoxAdapter(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: _emptyCard('لا توجد دورات مميزة حالياً'),
        ),
      );
    }
    final pageCount = (list.length + 1) ~/ 2;
    return SliverToBoxAdapter(
      child: SizedBox(
        height: 300,
        child: PageView.builder(
          controller: _topCoursesPageController,
          scrollDirection: Axis.horizontal,
          padEnds: true,
          physics: const BouncingScrollPhysics(parent: PageScrollPhysics()),
          itemCount: pageCount,
          itemBuilder: (context, pageIndex) {
            final i = pageIndex * 2;
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(left: 4),
                      child: _TopCourseCard(
                        index: i,
                        course: list[i],
                        isBookmarked: _wishlistIds.contains(list[i].id),
                        onBookmark: () => _toggleWishlist(list[i].id),
                        onTap: () {},
                        cardWidth: null,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: i + 1 < list.length
                        ? Padding(
                            padding: const EdgeInsets.only(right: 4),
                            child: _TopCourseCard(
                              index: i + 1,
                              course: list[i + 1],
                              isBookmarked: _wishlistIds.contains(list[i + 1].id),
                              onBookmark: () => _toggleWishlist(list[i + 1].id),
                              onTap: () {},
                              cardWidth: null,
                            ),
                          )
                        : const SizedBox.shrink(),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildRecentCourses(List<CourseItem> list) {
    if (list.isEmpty) {
      return SliverToBoxAdapter(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: _emptyCard('لا توجد دورات حديثة'),
        ),
      );
    }
    final pageCount = (list.length + 1) ~/ 2;
    return SliverToBoxAdapter(
      child: SizedBox(
        height: 300,
        child: PageView.builder(
          controller: _recentCoursesPageController,
          scrollDirection: Axis.horizontal,
          padEnds: true,
          physics: const BouncingScrollPhysics(parent: PageScrollPhysics()),
          itemCount: pageCount,
          itemBuilder: (context, pageIndex) {
            final i = pageIndex * 2;
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(left: 4),
                      child: _TopCourseCard(
                        index: i,
                        course: list[i],
                        isBookmarked: _wishlistIds.contains(list[i].id),
                        onBookmark: () => _toggleWishlist(list[i].id),
                        onTap: () {},
                        cardWidth: null,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: i + 1 < list.length
                        ? Padding(
                            padding: const EdgeInsets.only(right: 4),
                            child: _TopCourseCard(
                              index: i + 1,
                              course: list[i + 1],
                              isBookmarked: _wishlistIds.contains(list[i + 1].id),
                              onBookmark: () => _toggleWishlist(list[i + 1].id),
                              onTap: () {},
                              cardWidth: null,
                            ),
                          )
                        : const SizedBox.shrink(),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildCategoriesSection(List<CategoryWithCourses> categories) {
    if (categories.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());
    return SliverList(
      delegate: SliverChildBuilderDelegate(
        (context, index) {
          final cat = categories[index];
          return _buildOneCategorySection(cat, index);
        },
        childCount: categories.length,
      ),
    );
  }

  Widget _buildOneCategorySection(CategoryWithCourses cat, int sectionIndex) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 24, 20, 12),
          child: Row(
            children: [
              Text(
                '📂',
                style: const TextStyle(fontSize: 20),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  cat.name,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF333333),
                      ),
                ),
              ),
              if (cat.publishedCoursesCount > 0)
                Text(
                  '${cat.publishedCoursesCount} دورة',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                ),
            ],
          ),
        ),
        if (cat.courses.isEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            child: _emptyCard('لا توجد دورات في هذا القسم حالياً'),
          )
        else
          SizedBox(
            height: 292,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: cat.courses.length,
              itemBuilder: (context, i) {
                final course = cat.courses[i];
                return Padding(
                  padding: const EdgeInsets.only(left: 12, right: 4),
                  child: _TopCourseCard(
                    index: sectionIndex * 10 + i,
                    course: course,
                    isBookmarked: _wishlistIds.contains(course.id),
                    onBookmark: () => _toggleWishlist(course.id),
                    onTap: () {},
                    cardWidth: 220,
                  ),
                );
              },
            ),
          ),
        const SizedBox(height: 8),
      ],
    );
  }

  Widget _emptyCard(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Center(
        child: Text(
          text,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
        ),
      ),
    );
  }
}

class _HomeSliderSlide extends StatelessWidget {
  const _HomeSliderSlide({required this.slide});

  final HomeSlideItem slide;

  @override
  Widget build(BuildContext context) {
    final imageUrl = _fullImageUrl(slide.imageUrl);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (imageUrl != null && imageUrl.isNotEmpty)
              Image.network(
                imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => Container(color: AppTheme.primary),
              )
            else
              Container(color: AppTheme.primary),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(20),
                gradient: LinearGradient(
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                  colors: [
                    Colors.black.withValues(alpha: 0.6),
                    Colors.black.withValues(alpha: 0.25),
                    Colors.black.withValues(alpha: 0.05),
                  ],
                ),
              ),
            ),
            Positioned(
              left: 16,
              right: 16,
              bottom: 16,
              top: 16,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    slide.title.isNotEmpty ? slide.title : 'اكتشف أحدث الدورات والعروض',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      height: 1.25,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (slide.subtitle.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      slide.subtitle,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.white.withValues(alpha: 0.9),
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      if (slide.primaryText.isNotEmpty)
                        FilledButton(
                          onPressed: () => _onSlideAction(context, slide.primaryUrl),
                          style: FilledButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: AppTheme.primary,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          child: Text(slide.primaryText),
                        ),
                      if (slide.primaryText.isNotEmpty && slide.secondaryText.isNotEmpty) const SizedBox(width: 10),
                      if (slide.secondaryText.isNotEmpty)
                        OutlinedButton(
                          onPressed: () => _onSlideAction(context, slide.secondaryUrl),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(color: Colors.white54),
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          child: Text(slide.secondaryText),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _onSlideAction(BuildContext context, String url) {
    if (url.isEmpty) return;
    if (url.startsWith('http')) {
      // يمكن لاحقاً استخدام url_launcher لفتح الرابط
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('رابط: $url'), duration: const Duration(seconds: 2)),
      );
    } else {
      // مسار داخلي يمكن التعامل معه لاحقاً
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('رابط: $url'), duration: const Duration(seconds: 2)),
      );
    }
  }
}

class _TopCourseCard extends StatelessWidget {
  const _TopCourseCard({
    required this.index,
    required this.course,
    required this.isBookmarked,
    required this.onBookmark,
    required this.onTap,
    this.cardWidth,
  });

  final int index;
  final CourseItem course;
  final bool isBookmarked;
  final VoidCallback onBookmark;
  final VoidCallback onTap;
  final double? cardWidth;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.scaleInTween(),
      duration: Duration(milliseconds: 280 + (index * 40)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Transform.scale(
        scale: value,
        child: Material(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          elevation: 0,
          shadowColor: Colors.black.withValues(alpha: 0.08),
            child: InkWell(
            onTap: onTap,
            borderRadius: BorderRadius.circular(16),
            child: SizedBox(
              width: cardWidth,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Stack(
                    alignment: AlignmentDirectional.topEnd,
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                        child: _courseImage(course.coverImage, height: 120),
                      ),
                      Padding(
                        padding: const EdgeInsets.all(8),
                        child: GestureDetector(
                          onTap: onBookmark,
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.9),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              isBookmarked ? Icons.bookmark_rounded : Icons.bookmark_border_rounded,
                              size: 22,
                              color: AppTheme.primary,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          course.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF333333),
                              ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Text(
                              '${course.price.toStringAsFixed(0)} ر.س',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF333333),
                                  ),
                            ),
                            if (course.hasDiscount && course.originalPrice != null) ...[
                              const SizedBox(width: 6),
                              Text(
                                '${course.originalPrice!.toStringAsFixed(0)} ر.س',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Colors.grey,
                                      decoration: TextDecoration.lineThrough,
                                    ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Icon(Icons.star_rounded, size: 16, color: Colors.orange.shade700),
                            const SizedBox(width: 4),
                            Text(
                              '${course.rating}',
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFF333333),
                                  ),
                            ),
                            Container(
                              width: 1,
                              height: 12,
                              margin: const EdgeInsets.symmetric(horizontal: 8),
                              color: Colors.grey.shade400,
                            ),
                            Text(
                              _formatStudents(course.studentsCount),
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: const Color(0xFF333333),
                                  ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        if (course.category != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF3B82F6),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              course.category!.name,
                              style: const TextStyle(
                                fontSize: 12,
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _RecentCourseTile extends StatelessWidget {
  const _RecentCourseTile({
    required this.index,
    required this.course,
    required this.isBookmarked,
    required this.onBookmark,
    required this.onTap,
  });

  final int index;
  final CourseItem course;
  final bool isBookmarked;
  final VoidCallback onBookmark;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: AppTheme.fadeInTween(),
      duration: Duration(milliseconds: 260 + (index * 35)),
      curve: AppTheme.curveDefault,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.translate(
          offset: Offset(0, 12 * (1 - value)),
          child: Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            elevation: 0,
            shadowColor: Colors.black.withValues(alpha: 0.06),
            child: InkWell(
              onTap: onTap,
              borderRadius: BorderRadius.circular(16),
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: _courseImage(course.coverImage, width: 100, height: 80),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Text(
                                  course.title,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                        fontWeight: FontWeight.w600,
                                        color: const Color(0xFF333333),
                                      ),
                                ),
                              ),
                              GestureDetector(
                                onTap: onBookmark,
                                child: Icon(
                                  isBookmarked ? Icons.bookmark_rounded : Icons.bookmark_border_rounded,
                                  size: 22,
                                  color: isBookmarked ? Colors.black87 : AppTheme.primary,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Text(
                                '${course.price.toStringAsFixed(0)} ر.س',
                                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                      fontWeight: FontWeight.bold,
                                      color: const Color(0xFF333333),
                                    ),
                              ),
                              if (course.hasDiscount && course.originalPrice != null) ...[
                                const SizedBox(width: 6),
                                Text(
                                  '${course.originalPrice!.toStringAsFixed(0)} ر.س',
                                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                        color: Colors.grey,
                                        decoration: TextDecoration.lineThrough,
                                      ),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(Icons.star_rounded, size: 14, color: Colors.orange.shade700),
                              const SizedBox(width: 4),
                              Text(
                                '${course.rating}',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      fontWeight: FontWeight.w600,
                                      color: const Color(0xFF333333),
                                    ),
                              ),
                              Container(
                                width: 1,
                                height: 10,
                                margin: const EdgeInsets.symmetric(horizontal: 6),
                                color: Colors.grey.shade400,
                              ),
                              Text(
                                _formatStudents(course.studentsCount),
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: const Color(0xFF333333),
                                    ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          if (course.category != null)
                            Align(
                              alignment: AlignmentDirectional.centerEnd,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: _categoryColor(course.category!.name),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  course.category!.name,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: Colors.white,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

Widget _courseImage(String? url, {double? width, double? height}) {
  final fullUrl = _fullImageUrl(url);
  final w = width ?? double.infinity;
  final h = height ?? 120.0;
  if (fullUrl == null || fullUrl.isEmpty) {
    return Container(
      width: w == double.infinity ? null : w,
      height: h,
      color: Colors.grey.shade300,
      child: Icon(Icons.school_rounded, size: 48, color: Colors.grey.shade500),
    );
  }
  return Image.network(
    fullUrl,
    width: w == double.infinity ? null : w,
    height: h,
    fit: BoxFit.cover,
    loadingBuilder: (context, child, loadingProgress) {
      if (loadingProgress == null) return child;
      return Container(
        width: w == double.infinity ? null : w,
        height: h,
        color: Colors.grey.shade200,
        child: Center(
          child: SizedBox(
            width: 28,
            height: 28,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: AppTheme.primary,
              value: loadingProgress.expectedTotalBytes != null
                  ? loadingProgress.cumulativeBytesLoaded / (loadingProgress.expectedTotalBytes ?? 1)
                  : null,
            ),
          ),
        ),
      );
    },
    errorBuilder: (context, error, stackTrace) => Container(
      width: w == double.infinity ? null : w,
      height: h,
      color: Colors.grey.shade300,
      child: Icon(Icons.broken_image_outlined, size: 40, color: Colors.grey.shade500),
    ),
  );
}

/// يبني رابط الصورة الكامل إن كان الـ backend يعيد مساراً نسبياً
String? _fullImageUrl(String? url) {
  if (url == null || url.isEmpty) return null;
  final u = url.trim();
  if (u.startsWith('http://') || u.startsWith('https://')) return u;
  final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
  if (u.startsWith('/')) return '$base$u';
  // مسار مثل storage/courses/x.jpg أو courses/x.jpg
  if (u.startsWith('storage/')) return '$base/$u';
  return '$base/storage/$u';
}

String _formatStudents(int n) {
  if (n >= 1000) return '${(n / 1000).toStringAsFixed(n >= 10000 ? 0 : 1)}k طالب';
  return '$n طالب';
}

Color _categoryColor(String name) {
  final lower = name.toLowerCase();
  if (lower.contains('برمجة') || lower.contains('programming')) return const Color(0xFF3B82F6);
  if (lower.contains('طبخ') || lower.contains('cooking')) return const Color(0xFFEC4899);
  return AppTheme.primary;
}

import 'package:flutter/material.dart';
import '../api/auth_api.dart';
import '../api/courses_api.dart';
import '../api/config.dart';
import '../api/home_api.dart';
import '../api/wishlist_api.dart';
import '../app_theme.dart';
import 'course_detail_screen.dart';

/// شاشة عرض دورات تصنيف معين — تصميم احترافي مع حركات ظهور متتابعة
class CategoryCoursesScreen extends StatefulWidget {
  const CategoryCoursesScreen({
    super.key,
    required this.categoryId,
    required this.categoryName,
    this.subCategoryId,
    this.onWishlistCountChanged,
  });

  final int categoryId;
  final String categoryName;
  final int? subCategoryId;
  /// لتحديث عداد المفضلة في الهيدر عند الإضافة/الإزالة
  final void Function(int delta)? onWishlistCountChanged;

  @override
  State<CategoryCoursesScreen> createState() => _CategoryCoursesScreenState();
}

class _CategoryCoursesScreenState extends State<CategoryCoursesScreen> with SingleTickerProviderStateMixin {
  bool _loading = true;
  List<CourseItem> _courses = [];
  final Set<int> _wishlistCourseIds = {};
  late AnimationController _animController;
  static const int _staggerMs = 55;
  static const int _animDurationMs = 380;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: _animDurationMs + (50 * 15)),
    );
    _load();
  }

  Future<void> _toggleWishlist(int courseId) async {
    final isIn = _wishlistCourseIds.contains(courseId);
    final result = isIn
        ? await WishlistApi.removeCourse(courseId)
        : await WishlistApi.addCourse(courseId);
    if (result.isSuccess && mounted) {
      setState(() {
        if (isIn) _wishlistCourseIds.remove(courseId);
        else _wishlistCourseIds.add(courseId);
      });
      widget.onWishlistCountChanged?.call(isIn ? -1 : 1);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(isIn ? 'تمت الإزالة من المفضلة' : 'تم الإضافة في المفضلة'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else if (mounted) {
      final message = _wishlistErrorMessage(result);
      if (message != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  static String? _wishlistErrorMessage(WishlistOpResult result) {
    if (result.isUnauthorized) {
      return AuthApi.token != null
          ? 'انتهت الجلسة، يرجى تسجيل الدخول مرة أخرى'
          : 'يجب تسجيل الدخول لإضافة الدورة إلى المفضلة';
    }
    if (result.isNotFound) return 'الدورة غير متوفرة';
    if (result.isError) {
      final code = result.statusCode;
      return code != null ? 'حدث خطأ ($code)، حاول لاحقاً' : 'حدث خطأ، حاول لاحقاً';
    }
    return null;
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final results = await Future.wait([
      CoursesApi.getCoursesByCategory(
        categoryId: widget.categoryId,
        subCategoryId: widget.subCategoryId,
      ),
      WishlistApi.getWishlist(),
    ]);
    if (!mounted) return;
    final list = results[0] as List<CourseItem>;
    final wishlist = results[1] as WishlistResponse;
    setState(() {
      _courses = list;
      _wishlistCourseIds.clear();
      _wishlistCourseIds.addAll(wishlist.courseIds);
      _loading = false;
    });
    _animController.forward(from: 0);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
        slivers: [
          _buildAppBar(),
          if (_loading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
            )
          else if (_courses.isEmpty)
            SliverFillRemaining(
              child: _buildEmpty(),
            )
          else ...[
            SliverToBoxAdapter(child: _buildHeader()),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final course = _courses[index];
                    return _AnimatedCourseTile(
                      index: index,
                      course: course,
                      animation: _animController,
                      staggerMs: _staggerMs,
                      isInWishlist: _wishlistCourseIds.contains(course.id),
                      onToggleWishlist: () => _toggleWishlist(course.id),
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute<void>(
                            builder: (_) => CourseDetailScreen(
                              courseSlug: course.slug,
                              courseTitle: course.title,
                              initialIsInWishlist: _wishlistCourseIds.contains(course.id),
                              courseId: course.id,
                              onWishlistChanged: _load,
                              onWishlistCountChanged: widget.onWishlistCountChanged,
                            ),
                          ),
                        );
                      },
                    );
                  },
                  childCount: _courses.length,
                ),
              ),
            ),
          ],
        ],
        ),
      ),
    );
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 120,
      pinned: true,
      backgroundColor: AppTheme.primary,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Navigator.of(context).pop(),
      ),
      flexibleSpace: FlexibleSpaceBar(
        titlePadding: const EdgeInsets.only(left: 56, right: 20, bottom: 16),
        title: Text(
          widget.categoryName,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        background: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topRight,
              end: Alignment.bottomLeft,
              colors: [
                AppTheme.primary,
                AppTheme.primaryLight,
                const Color(0xFF5C2D91),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: AppTheme.primary.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.menu_book_rounded, size: 20, color: AppTheme.primary),
                const SizedBox(width: 8),
                Text(
                  '${_courses.length} دورة',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primary,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.school_outlined, size: 72, color: AppTheme.primary.withValues(alpha: 0.4)),
          const SizedBox(height: 20),
          Text(
            'لا توجد دورات في هذا التصنيف',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF4A4A4A),
                ),
          ),
        ],
      ),
    );
  }
}

class _AnimatedCourseTile extends StatelessWidget {
  const _AnimatedCourseTile({
    required this.index,
    required this.course,
    required this.animation,
    required this.staggerMs,
    required this.onTap,
    required this.isInWishlist,
    required this.onToggleWishlist,
  });

  final int index;
  final CourseItem course;
  final Animation<double> animation;
  final int staggerMs;
  final VoidCallback onTap;
  final bool isInWishlist;
  final VoidCallback onToggleWishlist;

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: animation,
      builder: (context, _) {
        final delay = index * staggerMs / 1000.0;
        final span = 0.35;
        final t = ((animation.value - delay) / span).clamp(0.0, 1.0);
        final curve = Curves.easeOutCubic.transform(t);
        final opacity = curve;
        final slide = 24.0 * (1 - curve);

        return Opacity(
          opacity: opacity,
          child: Transform.translate(
            offset: Offset(0, slide),
            child: Padding(
              padding: const EdgeInsets.only(bottom: 14),
              child: _CourseCard(
                course: course,
                onTap: onTap,
                isInWishlist: isInWishlist,
                onToggleWishlist: onToggleWishlist,
              ),
            ),
          ),
        );
      },
    );
  }
}

class _CourseCard extends StatelessWidget {
  const _CourseCard({
    required this.course,
    required this.onTap,
    required this.isInWishlist,
    required this.onToggleWishlist,
  });

  final CourseItem course;
  final VoidCallback onTap;
  final bool isInWishlist;
  final VoidCallback onToggleWishlist;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      elevation: 0,
      shadowColor: Colors.black.withValues(alpha: 0.08),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            textDirection: TextDirection.rtl,
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.horizontal(right: Radius.circular(20)),
                child: _courseImage(course.coverImage, width: 120, height: 120),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        course.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF1A1A1A),
                              height: 1.3,
                            ),
                      ),
                      if (course.instructor != null) ...[
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Icon(Icons.person_outline_rounded, size: 14, color: Colors.grey.shade600),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                course.instructor!.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Colors.grey.shade600,
                                    ),
                              ),
                            ),
                          ],
                        ),
                      ],
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Icon(Icons.star_rounded, size: 18, color: Colors.orange.shade700),
                          const SizedBox(width: 4),
                          Text(
                            '${course.rating}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1A1A1A),
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${course.studentsCount} طالب',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Text(
                            '${course.price.toStringAsFixed(0)} ر.س',
                            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primary,
                                ),
                          ),
                          if (course.hasDiscount && course.originalPrice != null) ...[
                            const SizedBox(width: 8),
                            Text(
                              '${course.originalPrice!.toStringAsFixed(0)} ر.س',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                                decoration: TextDecoration.lineThrough,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              IconButton(
                icon: Icon(
                  isInWishlist ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                  color: isInWishlist ? Colors.redAccent : Colors.grey.shade500,
                  size: 24,
                ),
                onPressed: onToggleWishlist,
                tooltip: isInWishlist ? 'إزالة من المفضلة' : 'إضافة للمفضلة',
              ),
              Padding(
                padding: const EdgeInsets.only(top: 14, left: 8),
                child: Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey.shade400),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _courseImage(String? url, {required double width, required double height}) {
    final fullUrl = _fullImageUrl(url);
    if (fullUrl == null || fullUrl.isEmpty) {
      return Container(
        width: width,
        height: height,
        color: Colors.grey.shade300,
        child: Icon(Icons.school_rounded, size: 40, color: Colors.grey.shade500),
      );
    }
    return Image.network(
      fullUrl,
      width: width,
      height: height,
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(
        width: width,
        height: height,
        color: Colors.grey.shade300,
        child: Icon(Icons.school_rounded, size: 40, color: Colors.grey.shade500),
      ),
    );
  }

  String? _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    final u = url.trim();
    if (u.startsWith('http://') || u.startsWith('https://')) return u;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    if (u.startsWith('/')) return '$base$u';
    if (u.startsWith('storage/')) return '$base/$u';
    return '$base/storage/$u';
  }
}

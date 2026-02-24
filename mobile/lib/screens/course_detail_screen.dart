import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../api/courses_api.dart';
import '../api/config.dart';
import '../api/wishlist_api.dart';
import '../api/cart_api.dart';
import '../app_theme.dart';
import 'cart_screen.dart';

/// شاشة تفاصيل الدورة التدريبية — تصميم احترافي مع تبويبات وحركات (مشابه للصورة المرجعية)
class CourseDetailScreen extends StatefulWidget {
  const CourseDetailScreen({
    super.key,
    required this.courseSlug,
    this.courseTitle,
    this.initialIsInWishlist = false,
    this.courseId,
    this.onWishlistChanged,
  });

  final String courseSlug;
  final String? courseTitle;
  final bool initialIsInWishlist;
  final int? courseId;
  final VoidCallback? onWishlistChanged;

  @override
  State<CourseDetailScreen> createState() => _CourseDetailScreenState();
}

class _CourseDetailScreenState extends State<CourseDetailScreen> with SingleTickerProviderStateMixin {
  CourseDetailItem? _course;
  bool _loading = true;
  String? _error;
  bool _isBookmarked = false;
  late TabController _tabController;
  final List<String> _tabs = ['عن الدورة', 'الدروس', 'التقييمات'];

  @override
  void initState() {
    super.initState();
    _isBookmarked = widget.initialIsInWishlist;
    _tabController = TabController(length: _tabs.length, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final course = await CoursesApi.getCourseBySlug(widget.courseSlug);
    if (!mounted) return;
    bool inWishlist = widget.initialIsInWishlist;
    if (course != null && widget.courseId == null) {
      final res = await WishlistApi.getWishlist();
      inWishlist = res.courseIds.contains(course.id);
    } else if (course != null && widget.courseId != null) {
      inWishlist = widget.initialIsInWishlist;
    }
    setState(() {
      _course = course;
      _isBookmarked = inWishlist;
      _loading = false;
      _error = course == null ? 'لم يتم العثور على الدورة' : null;
    });
  }

  Future<void> _toggleWishlist() async {
    final id = widget.courseId ?? _course?.id;
    if (id == null) return;
    final ok = _isBookmarked
        ? await WishlistApi.removeCourse(id)
        : await WishlistApi.addCourse(id);
    if (ok && mounted) {
      setState(() => _isBookmarked = !_isBookmarked);
      widget.onWishlistChanged?.call();
    }
  }

  Future<void> _openPreviewVideo() async {
    final url = _course?.previewVideoUrl;
    if (url == null || url.isEmpty) return;
    String link = url.trim();
    if (!link.startsWith('http://') && !link.startsWith('https://')) {
      link = 'https://www.youtube.com/watch?v=$link';
    }
    final uri = Uri.tryParse(link);
    if (uri != null && await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        backgroundColor: const Color(0xFFF5F3F8),
        appBar: AppBar(
          backgroundColor: AppTheme.primary,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
            onPressed: () => Navigator.of(context).pop(),
          ),
          title: Text(
            widget.courseTitle ?? 'تفاصيل الدورة',
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
          ),
        ),
        body: const Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }

    if (_error != null || _course == null) {
      return Scaffold(
        appBar: AppBar(leading: BackButton(onPressed: () => Navigator.of(context).pop())),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(_error ?? 'حدث خطأ', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _load,
                style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                child: const Text('إعادة المحاولة'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          _buildSliverAppBar(),
          SliverToBoxAdapter(child: _buildInfoCard()),
          SliverPersistentHeader(
            pinned: true,
            delegate: _TabBarDelegate(
              tabController: _tabController,
              tabs: _tabs,
            ),
          ),
        ],
        body: TabBarView(
          controller: _tabController,
          children: [
            _AboutTab(course: _course!),
            _LessonsTab(course: _course!),
            _ReviewsTab(course: _course!),
          ],
        ),
      ),
      bottomNavigationBar: _buildCourseBottomBar(),
    );
  }

  Widget _buildCourseBottomBar() {
    final c = _course!;
    return Container(
      padding: EdgeInsets.fromLTRB(20, 12, 20, 12 + MediaQuery.of(context).padding.bottom),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 12, offset: const Offset(0, -4))],
      ),
      child: SafeArea(
        child: FilledButton(
          onPressed: () => _addCourseToCart(c),
          style: FilledButton.styleFrom(
            backgroundColor: AppTheme.primary,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          ),
          child: const Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.shopping_cart_rounded),
              SizedBox(width: 8),
              Text('إضافة إلى السلة', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _addCourseToCart(CourseDetailItem c) async {
    final ok = await CartApi.addCourse(c.id);
    if (!mounted) return;
    if (ok) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('تمت إضافة الدورة إلى السلة'),
          action: SnackBarAction(
            label: 'فتح السلة',
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CartScreen())),
          ),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يجب تسجيل الدخول لإضافة الدورة إلى السلة')),
      );
    }
  }

  Widget _buildSliverAppBar() {
    final coverUrl = _fullImageUrl(_course!.coverImage);
    return SliverAppBar(
      expandedHeight: 220,
      pinned: true,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Navigator.of(context).pop(),
      ),
      actions: [
        IconButton(
          icon: Icon(
            _isBookmarked ? Icons.favorite_rounded : Icons.favorite_border_rounded,
            color: _isBookmarked ? Colors.redAccent : Colors.white,
          ),
          onPressed: _toggleWishlist,
          tooltip: _isBookmarked ? 'إزالة من المفضلة' : 'إضافة للمفضلة',
        ),
      ],
      flexibleSpace: FlexibleSpaceBar(
        background: Stack(
          fit: StackFit.expand,
          children: [
            if (coverUrl != null && coverUrl.isNotEmpty)
              Image.network(
                coverUrl,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => _coverPlaceholder(),
              )
            else
              _coverPlaceholder(),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.6),
                  ],
                ),
              ),
            ),
            Center(
              child: Material(
                color: Colors.white.withValues(alpha: 0.95),
                shape: const CircleBorder(),
                child: InkWell(
                  onTap: _openPreviewVideo,
                  customBorder: const CircleBorder(),
                  child: const Padding(
                    padding: EdgeInsets.all(20),
                    child: Icon(Icons.play_arrow_rounded, size: 48, color: AppTheme.primary),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _coverPlaceholder() {
    return Container(
      color: AppTheme.primary,
      child: Icon(Icons.school_rounded, size: 80, color: Colors.white.withValues(alpha: 0.5)),
    );
  }

  Widget _buildInfoCard() {
    final c = _course!;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 0),
      transform: Matrix4.translationValues(0, -24, 0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 24,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    c.title,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF1A1A1A),
                          height: 1.3,
                        ),
                  ),
                ),
                IconButton(
                  onPressed: _toggleWishlist,
                  icon: Icon(
                    _isBookmarked ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                    color: _isBookmarked ? Colors.redAccent : AppTheme.primary,
                    size: 28,
                  ),
                  tooltip: _isBookmarked ? 'إزالة من المفضلة' : 'إضافة للمفضلة',
                ),
              ],
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (c.category != null)
                  _ChipLabel(
                    label: c.category!.name,
                    color: const Color(0xFF2563EB),
                  ),
                if (c.subCategory != null)
                  _ChipLabel(
                    label: c.subCategory!.name,
                    color: const Color(0xFF059669),
                  ),
                if (c.levelLabel != null && c.levelLabel!.isNotEmpty)
                  _ChipLabel(
                    label: c.levelLabel!,
                    color: AppTheme.primary,
                  ),
              ],
            ),
            if (c.instructor != null) ...[
              const SizedBox(height: 14),
              Row(
                textDirection: TextDirection.rtl,
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: AppTheme.primary.withValues(alpha: 0.12),
                    backgroundImage: c.instructor!.avatar != null && c.instructor!.avatar!.isNotEmpty
                        ? NetworkImage(_fullImageUrl(c.instructor!.avatar) ?? '')
                        : null,
                    child: c.instructor!.avatar == null || c.instructor!.avatar!.isEmpty
                        ? Text(
                            c.instructor!.name.isNotEmpty ? c.instructor!.name.substring(0, 1).toUpperCase() : '?',
                            style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.bold),
                          )
                        : null,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'المدرب',
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        ),
                        Text(
                          c.instructor!.name,
                          style: const TextStyle(
                            fontWeight: FontWeight.w600,
                            fontSize: 15,
                            color: Color(0xFF1A1A1A),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 16),
            Row(
              children: [
                Icon(Icons.star_rounded, size: 20, color: Colors.orange.shade700),
                const SizedBox(width: 4),
                Text(
                  '${c.rating}',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const SizedBox(width: 6),
                Text(
                  '${c.reviewsCount} تقييم',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                ),
                const Spacer(),
                Text(
                  '${c.price.toStringAsFixed(0)} ر.س',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary,
                      ),
                ),
                if (c.hasDiscount && c.originalPrice != null) ...[
                  const SizedBox(width: 6),
                  Text(
                    '${c.originalPrice!.toStringAsFixed(0)} ر.س',
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey.shade600,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _StatChip(icon: Icons.people_outline_rounded, label: '${c.studentsCount} طالب'),
                const SizedBox(width: 12),
                _StatChip(icon: Icons.schedule_rounded, label: '+${c.hours} ساعة'),
                const SizedBox(width: 12),
                _StatChip(icon: Icons.folder_outlined, label: '${c.lessonsCount} درس'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String? _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    final u = url.trim();
    if (u.startsWith('http')) return u;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    if (u.startsWith('/')) return '$base$u';
    if (u.startsWith('storage/')) return '$base/$u';
    return '$base/storage/$u';
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 18, color: Colors.grey.shade600),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
      ],
    );
  }
}

class _ChipLabel extends StatelessWidget {
  const _ChipLabel({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(fontWeight: FontWeight.w600, color: color, fontSize: 13),
      ),
    );
  }
}

class _TabBarDelegate extends SliverPersistentHeaderDelegate {
  _TabBarDelegate({required this.tabController, required this.tabs});

  final TabController tabController;
  final List<String> tabs;

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return Container(
      color: Colors.white,
      child: TabBar(
        controller: tabController,
        labelColor: AppTheme.primary,
        unselectedLabelColor: Colors.grey.shade600,
        indicatorColor: AppTheme.primary,
        indicatorWeight: 3,
        labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
        tabs: tabs.map((t) => Tab(text: t)).toList(),
      ),
    );
  }

  @override
  double get maxExtent => 48;

  @override
  double get minExtent => 48;

  @override
  bool shouldRebuild(covariant SliverPersistentHeaderDelegate oldDelegate) => false;
}

class _AboutTab extends StatefulWidget {
  const _AboutTab({required this.course});

  final CourseDetailItem course;

  @override
  State<_AboutTab> createState() => _AboutTabState();
}

class _AboutTabState extends State<_AboutTab> with SingleTickerProviderStateMixin {
  late AnimationController _anim;
  late Animation<double> _fade;

  @override
  void initState() {
    super.initState();
    _anim = AnimationController(vsync: this, duration: const Duration(milliseconds: 400));
    _fade = CurvedAnimation(parent: _anim, curve: Curves.easeOutCubic);
    _anim.forward();
  }

  @override
  void dispose() {
    _anim.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final c = widget.course;
    return FadeTransition(
      opacity: _fade,
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (c.announcement != null && c.announcement!.trim().isNotEmpty) ...[
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppTheme.primary.withValues(alpha: 0.2)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      textDirection: TextDirection.rtl,
                      children: [
                        Icon(Icons.campaign_rounded, size: 22, color: AppTheme.primary),
                        const SizedBox(width: 8),
                        Text(
                          'إعلان الدورة',
                          style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primary,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      c.announcement!,
                      style: TextStyle(
                        fontSize: 14,
                        height: 1.5,
                        color: Colors.grey.shade800,
                      ),
                      textDirection: TextDirection.rtl,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
            ],
            Text(
              'عن الدورة',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF1A1A1A),
                  ),
            ),
            const SizedBox(height: 12),
            Text(
              c.description.isEmpty ? 'لا يوجد وصف متاح لهذه الدورة.' : c.description,
              style: TextStyle(
                fontSize: 15,
                height: 1.6,
                color: Colors.grey.shade800,
              ),
              textDirection: TextDirection.rtl,
            ),
            if (c.objectives.isNotEmpty) ...[
              const SizedBox(height: 28),
              Text(
                'أهداف الدورة',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF1A1A1A),
                    ),
              ),
              const SizedBox(height: 12),
              Text(
                c.objectives,
                style: TextStyle(
                  fontSize: 15,
                  height: 1.6,
                  color: Colors.grey.shade800,
                ),
                textDirection: TextDirection.rtl,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _LessonsTab extends StatelessWidget {
  const _LessonsTab({required this.course});

  final CourseDetailItem course;

  @override
  Widget build(BuildContext context) {
    final sections = course.sections;
    if (sections.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.play_circle_outline_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.5)),
            const SizedBox(height: 16),
            Text(
              '${course.lessonsCount} درس',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
            ),
            const SizedBox(height: 8),
            Text(
              'لا توجد أقسام أو دروس معروضة حالياً',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      itemCount: sections.length,
      itemBuilder: (context, sectionIndex) {
        final section = sections[sectionIndex];
        return Padding(
          padding: const EdgeInsets.only(bottom: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.only(right: 4, bottom: 10),
                child: Row(
                  textDirection: TextDirection.rtl,
                  children: [
                    Icon(Icons.folder_rounded, size: 20, color: AppTheme.primary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        section.title.isEmpty ? 'القسم ${sectionIndex + 1}' : section.title,
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF1A1A1A),
                            ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${section.lessons.length} درس',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.primary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              ...section.lessons.asMap().entries.map((entry) {
                final lesson = entry.value;
                return Container(
                  margin: const EdgeInsets.only(right: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: Colors.grey.shade200),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.04),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () {
                        // TODO: فتح الدرس عند توفر شاشة المشاهدة
                      },
                      borderRadius: BorderRadius.circular(14),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        child: Row(
                          textDirection: TextDirection.rtl,
                          children: [
                            Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: AppTheme.primary.withValues(alpha: 0.1),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                lesson.isFreePreview ? Icons.play_circle_filled_rounded : Icons.play_circle_outline_rounded,
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
                                    lesson.title.isEmpty ? 'الدرس ${entry.key + 1}' : lesson.title,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w600,
                                      fontSize: 14,
                                      color: Color(0xFF1A1A1A),
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    textDirection: TextDirection.rtl,
                                  ),
                                  if (lesson.durationMinutes > 0) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      lesson.durationLabel,
                                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                            if (lesson.isFreePreview)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF059669).withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: const Text(
                                  'معاينة مجانية',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF059669),
                                  ),
                                ),
                              ),
                            const SizedBox(width: 8),
                            Icon(Icons.chevron_left_rounded, color: Colors.grey.shade400),
                          ],
                        ),
                      ),
                    ),
                  ),
                );
              }),
            ],
          ),
        );
      },
    );
  }
}

class _ReviewsTab extends StatelessWidget {
  const _ReviewsTab({required this.course});

  final CourseDetailItem course;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.star_rounded, size: 64, color: Colors.orange.shade300),
          const SizedBox(height: 16),
          Text(
            '${course.rating} من 5',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            '${course.reviewsCount} تقييم',
            style: TextStyle(color: Colors.grey.shade600),
          ),
          const SizedBox(height: 24),
          Text(
            'التقييمات ستُعرض هنا عند ربطها بالـ API',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../category_courses_screen.dart';
import '../../api/courses_api.dart';
import '../../app_theme.dart';

/// تبويب الدورات: تصنيفات من إدارة الدورات التدريبية بتصميم احترافي
class CoursesTab extends StatefulWidget {
  const CoursesTab({super.key, this.onOpenDrawer, this.wishlistCount = 0, this.onWishlistCountChanged, this.onOpenFavorite, this.cartCount = 0, this.notificationsCount = 0, this.messagesCount = 0, this.onOpenCart, this.onOpenNotifications, this.onOpenMessages});

  final VoidCallback? onOpenDrawer;
  final int wishlistCount;
  final void Function(int delta)? onWishlistCountChanged;
  final VoidCallback? onOpenFavorite;
  final int cartCount;
  final int notificationsCount;
  final int messagesCount;
  final VoidCallback? onOpenCart;
  final VoidCallback? onOpenNotifications;
  final VoidCallback? onOpenMessages;

  @override
  State<CoursesTab> createState() => _CoursesTabState();
}

class _CoursesTabState extends State<CoursesTab> {
  bool _loading = true;
  List<CourseCategoryItem> _categories = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await CoursesApi.getCategories();
    if (!mounted) return;
    setState(() {
      _categories = res.categories;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      appBar: AppHeader(
        title: 'الدورات',
        onMenu: widget.onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
        favoriteCount: widget.wishlistCount,
        onFavorite: widget.onOpenFavorite,
        cartCount: widget.cartCount,
        notificationsCount: widget.notificationsCount,
        messagesCount: widget.messagesCount,
        onCart: widget.onOpenCart,
        onBell: widget.onOpenNotifications,
        onMessages: widget.onOpenMessages,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _categories.isEmpty
                ? _buildEmpty()
                : CustomScrollView(
                    physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
                    slivers: [
                      SliverToBoxAdapter(child: _buildHero()),
                      SliverPadding(
                        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
                        sliver: SliverToBoxAdapter(
                          child: Text(
                            'تصفح حسب التصنيف',
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.w600,
                                  color: const Color(0xFF4A4A4A),
                                ),
                          ),
                        ),
                      ),
                      SliverPadding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        sliver: SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) {
                              final cat = _categories[index];
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: _CourseCategoryCard(
                                  category: cat,
                                  onWishlistCountChanged: widget.onWishlistCountChanged,
                                  onTap: () {
                                    Navigator.of(context).push(
                                      MaterialPageRoute<void>(
                                        builder: (_) => CategoryCoursesScreen(
                                          categoryId: cat.id,
                                          categoryName: cat.name,
                                          onWishlistCountChanged: widget.onWishlistCountChanged,
                                        ),
                                      ),
                                    );
                                  },
                                ),
                              );
                            },
                            childCount: _categories.length,
                          ),
                        ),
                      ),
                      const SliverToBoxAdapter(child: SizedBox(height: 24)),
                    ],
                  ),
      ),
    );
  }

  Widget _buildHero() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
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
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.35),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.school_rounded, size: 40, color: Colors.white.withValues(alpha: 0.95)),
          const SizedBox(height: 12),
          Text(
            'اختر التصنيف المناسب',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                  height: 1.3,
                ),
          ),
          const SizedBox(height: 4),
          Text(
            'تصفح مئات الدورات ضمن تصنيفات منظمة',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Colors.white.withValues(alpha: 0.9),
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
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppTheme.primary.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.folder_off_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.5)),
          ),
          const SizedBox(height: 24),
          Text(
            'لا توجد تصنيفات حالياً',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF4A4A4A),
                ),
          ),
          const SizedBox(height: 8),
          Text(
            'ستظهر هنا تصنيفات الدورات من إدارة الدورات التدريبية',
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }
}

class _CourseCategoryCard extends StatelessWidget {
  const _CourseCategoryCard({
    required this.category,
    required this.onTap,
    this.onWishlistCountChanged,
  });

  final CourseCategoryItem category;
  final VoidCallback onTap;
  final void Function(int delta)? onWishlistCountChanged;

  static const List<Color> _cardGradients = [
    Color(0xFF2c004d),
    Color(0xFF4a1a6d),
    Color(0xFF2563eb),
    Color(0xFF059669),
    Color(0xFF7c3aed),
    Color(0xFFdc2626),
  ];

  @override
  Widget build(BuildContext context) {
    final color = _cardGradients[category.name.hashCode.abs() % _cardGradients.length];
    final hasChildren = category.children.isNotEmpty;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.06),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(20),
                child: Row(
                  children: [
                    _buildThumb(color),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            category.name,
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF1A1A1A),
                                ),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(Icons.play_circle_outline_rounded, size: 18, color: color),
                              const SizedBox(width: 6),
                              Text(
                                '${category.publishedCoursesCount} دورة',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                      color: Colors.grey.shade600,
                                      fontWeight: FontWeight.w500,
                                    ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey.shade400),
                  ],
                ),
              ),
              if (hasChildren) ...[
                Divider(height: 1, color: Colors.grey.shade200, indent: 20, endIndent: 20),
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: category.children.take(5).map((child) {
                      return InkWell(
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) => CategoryCoursesScreen(
                                categoryId: category.id,
                                categoryName: child.name,
                                subCategoryId: child.id,
                                onWishlistCountChanged: onWishlistCountChanged,
                              ),
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            '${child.name} (${child.publishedCoursesCount})',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                  color: color,
                                  fontWeight: FontWeight.w500,
                                ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildThumb(Color color) {
    if (category.image != null && category.image!.isNotEmpty) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Image.network(
          category.image!,
          width: 64,
          height: 64,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _iconPlaceholder(color),
        ),
      );
    }
    return _iconPlaceholder(color);
  }

  Widget _iconPlaceholder(Color color) {
    return Container(
      width: 64,
      height: 64,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            color,
            color.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.3),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Icon(Icons.menu_book_rounded, size: 32, color: Colors.white),
    );
  }
}

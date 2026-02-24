import 'package:flutter/material.dart';
import '../api/config.dart';
import '../api/home_api.dart';
import '../api/store_api.dart';
import '../api/wishlist_api.dart';
import '../app_theme.dart';
import 'course_detail_screen.dart';
import 'feature_scaffold.dart';
import 'product_detail_screen.dart';

/// شاشة المفضلة — دورات + منتجات مع إمكانية الإزالة
class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  bool _loading = true;
  List<CourseItem> _courses = [];
  List<StoreProductItem> _products = [];
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _needsAuth = false;
    });
    final res = await WishlistApi.getWishlist();
    if (mounted) {
      setState(() {
        _courses = res.courses;
        _products = res.products;
        _needsAuth = res.needsAuth;
        _loading = false;
      });
    }
  }

  Future<void> _removeCourse(int courseId) async {
    final ok = await WishlistApi.removeCourse(courseId);
    if (ok && mounted) {
      setState(() => _courses = _courses.where((c) => c.id != courseId).toList());
    }
  }

  Future<void> _removeProduct(int productId) async {
    final ok = await WishlistApi.removeProduct(productId);
    if (ok && mounted) {
      setState(() => _products = _products.where((p) => p.id != productId).toList());
    }
  }

  String _fullImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'المفضلة',
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _courses.isEmpty && _products.isEmpty
                ? _buildEmptyState()
                : CustomScrollView(
                    slivers: [
                      if (_courses.isNotEmpty) ...[
                        SliverToBoxAdapter(
                          child: Padding(
                            padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                            child: Row(
                              textDirection: TextDirection.rtl,
                              children: [
                                Icon(Icons.school_rounded, color: AppTheme.primary, size: 22),
                                const SizedBox(width: 8),
                                Text(
                                  'دوراتي المفضلة (${_courses.length})',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryDark,
                                      ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (_, i) => _WishlistCourseTile(
                              course: _courses[i],
                              imageUrl: _fullImageUrl(_courses[i].coverImage),
                              onTap: () => Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => CourseDetailScreen(
                                    courseSlug: _courses[i].slug,
                                    courseTitle: _courses[i].title,
                                  ),
                                ),
                              ),
                              onRemove: () => _removeCourse(_courses[i].id),
                            ),
                            childCount: _courses.length,
                          ),
                        ),
                        const SliverToBoxAdapter(child: SizedBox(height: 24)),
                      ],
                      if (_products.isNotEmpty) ...[
                        SliverToBoxAdapter(
                          child: Padding(
                            padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
                            child: Row(
                              textDirection: TextDirection.rtl,
                              children: [
                                Icon(Icons.shopping_bag_rounded, color: AppTheme.primary, size: 22),
                                const SizedBox(width: 8),
                                Text(
                                  'منتجاتي المفضلة (${_products.length})',
                                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryDark,
                                      ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (_, i) => _WishlistProductTile(
                              product: _products[i],
                              imageUrl: _fullImageUrl(_products[i].mainImage),
                              onTap: () => Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => ProductDetailScreen(
                                    productSlug: _products[i].slug,
                                    productName: _products[i].name,
                                  ),
                                ),
                              ),
                              onRemove: () => _removeProduct(_products[i].id),
                            ),
                            childCount: _products.length,
                          ),
                        ),
                        const SliverToBoxAdapter(child: SizedBox(height: 32)),
                      ],
                    ],
                  ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: _AnimatedEmptyState(
        icon: Icons.favorite_border_rounded,
        message: 'المفضلة فارغة',
        subtitle: _needsAuth
            ? 'سجّل الدخول لإضافة دورات ومنتجات للمفضلة'
            : 'أضف دورات أو منتجات من أيقونة القلب لتظهر هنا',
      ),
    );
  }
}

class _WishlistCourseTile extends StatelessWidget {
  const _WishlistCourseTile({
    required this.course,
    required this.imageUrl,
    required this.onTap,
    required this.onRemove,
  });

  final CourseItem course;
  final String imageUrl;
  final VoidCallback onTap;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        elevation: 2,
        shadowColor: Colors.black26,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.horizontal(right: Radius.circular(16)),
                  child: Image.network(
                    imageUrl,
                    width: 100,
                    height: 80,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 100,
                      height: 80,
                      color: Colors.grey.shade200,
                      child: const Icon(Icons.school_rounded, size: 36),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        course.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppTheme.primaryDark,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textDirection: TextDirection.rtl,
                      ),
                      if (course.instructor != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          course.instructor!.name,
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          textDirection: TextDirection.rtl,
                        ),
                      ],
                      const SizedBox(height: 6),
                      Row(
                        textDirection: TextDirection.rtl,
                        children: [
                          Text(
                            '${course.price} ر.س',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary,
                              fontSize: 14,
                            ),
                          ),
                          if (course.hasDiscount) ...[
                            const SizedBox(width: 8),
                            Text(
                              '${course.originalPrice} ر.س',
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
                IconButton(
                  icon: const Icon(Icons.favorite_rounded, color: Colors.redAccent),
                  onPressed: onRemove,
                  tooltip: 'إزالة من المفضلة',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _WishlistProductTile extends StatelessWidget {
  const _WishlistProductTile({
    required this.product,
    required this.imageUrl,
    required this.onTap,
    required this.onRemove,
  });

  final StoreProductItem product;
  final String imageUrl;
  final VoidCallback onTap;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        elevation: 2,
        shadowColor: Colors.black26,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              textDirection: TextDirection.rtl,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.horizontal(right: Radius.circular(16)),
                  child: Image.network(
                    imageUrl,
                    width: 100,
                    height: 80,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 100,
                      height: 80,
                      color: Colors.grey.shade200,
                      child: const Icon(Icons.shopping_bag_rounded, size: 36),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        product.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppTheme.primaryDark,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textDirection: TextDirection.rtl,
                      ),
                      if (product.category != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          product.category!.name,
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          textDirection: TextDirection.rtl,
                        ),
                      ],
                      const SizedBox(height: 6),
                      Row(
                        textDirection: TextDirection.rtl,
                        children: [
                          Text(
                            '${product.price} ر.س',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary,
                              fontSize: 14,
                            ),
                          ),
                          if (product.comparePrice != null && product.comparePrice! > product.price) ...[
                            const SizedBox(width: 8),
                            Text(
                              '${product.comparePrice} ر.س',
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
                IconButton(
                  icon: const Icon(Icons.favorite_rounded, color: Colors.redAccent),
                  onPressed: onRemove,
                  tooltip: 'إزالة من المفضلة',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _AnimatedEmptyState extends StatelessWidget {
  const _AnimatedEmptyState({
    required this.icon,
    required this.message,
    this.subtitle,
  });

  final IconData icon;
  final String message;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0, end: 1),
      duration: AppTheme.animSlow,
      curve: AppTheme.curveEmphasized,
      builder: (context, value, _) => Opacity(
        opacity: value,
        child: Transform.scale(
          scale: value,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(28),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.08),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
                ),
                const SizedBox(height: 24),
                Text(
                  message,
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                ),
                if (subtitle != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    subtitle!,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

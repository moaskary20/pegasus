import 'package:flutter/material.dart';
import '../api/cart_api.dart';
import '../api/config.dart';
import '../app_theme.dart';
import 'checkout_screen.dart';
import 'course_detail_screen.dart';
import 'feature_scaffold.dart';
import 'product_detail_screen.dart';

/// سلة المشتريات — دورات + منتجات مع إمكانية الإزالة والمجموع
class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  bool _loading = true;
  List<CartCourseItem> _courses = [];
  List<CartProductItem> _products = [];
  double _coursesSubtotal = 0;
  double _productsSubtotal = 0;
  double _total = 0;
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await CartApi.getCart();
    if (mounted) {
      setState(() {
        _courses = res.courses;
        _products = res.cartProducts;
        _coursesSubtotal = res.coursesSubtotal;
        _productsSubtotal = res.productsSubtotal;
        _total = res.total;
        _needsAuth = res.needsAuth;
        _loading = false;
      });
    }
  }

  Future<void> _removeCourse(int courseId) async {
    final ok = await CartApi.removeCourse(courseId);
    if (ok && mounted) {
      await _load();
    }
  }

  Future<void> _removeProduct(int cartItemId) async {
    final ok = await CartApi.removeProduct(cartItemId);
    if (ok && mounted) {
      await _load();
    }
  }

  String _fullUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    final minHeight = MediaQuery.sizeOf(context).height -
        (kToolbarHeight + MediaQuery.paddingOf(context).top + 8);
    return FeatureScaffold(
      title: 'سلة المشتريات',
      body: SizedBox(
        height: minHeight,
        child: RefreshIndicator(
          onRefresh: _load,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
            slivers: [
              if (_loading)
                const SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                )
              else if (_needsAuth)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: _buildNeedsAuth(),
                )
              else if (_courses.isEmpty && _products.isEmpty)
                SliverFillRemaining(
                  hasScrollBody: false,
                  child: _buildEmptyState(),
                )
              else ...[
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
                            'دورات السلة (${_courses.length})',
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
                      (_, i) => _CartCourseTile(
                        course: _courses[i],
                        imageUrl: _fullUrl(_courses[i].coverImage),
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
                            'منتجات السلة (${_products.length})',
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
                      (_, i) => _CartProductTile(
                        item: _products[i],
                        imageUrl: _fullUrl(_products[i].mainImage),
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
                  const SliverToBoxAdapter(child: SizedBox(height: 24)),
                ],
                SliverToBoxAdapter(child: _buildSummary()),
                const SliverToBoxAdapter(child: SizedBox(height: 32)),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSummary() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20),
      padding: const EdgeInsets.all(20),
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
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'ملخص السلة',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryDark,
                ),
          ),
          const SizedBox(height: 12),
          if (_courses.isNotEmpty)
            _SummaryRow(label: 'مجموع الدورات', value: '${_coursesSubtotal.toStringAsFixed(1)} ر.س'),
          if (_products.isNotEmpty)
            _SummaryRow(label: 'مجموع المنتجات', value: '${_productsSubtotal.toStringAsFixed(1)} ر.س'),
          const Divider(height: 24),
          _SummaryRow(
            label: 'الإجمالي',
            value: '${_total.toStringAsFixed(1)} ر.س',
            valueStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppTheme.primary),
          ),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _total > 0
                ? () => Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => CheckoutScreen(initialCartData: CartScreenCartData(
                          courses: _courses,
                          products: _products,
                          coursesSubtotal: _coursesSubtotal,
                          productsSubtotal: _productsSubtotal,
                          total: _total,
                        )),
                      ),
                    )
                : null,
            style: FilledButton.styleFrom(
              backgroundColor: AppTheme.primary,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            child: const Text('متابعة للدفع', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Padding(
      padding: const EdgeInsets.all(20),
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
              child: Icon(Icons.shopping_cart_outlined, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            ),
            const SizedBox(height: 24),
            Text(
              'السلة فارغة',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'أضف دورات أو منتجات من صفحات التفاصيل لتظهر هنا',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNeedsAuth() {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.login_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
            const SizedBox(height: 24),
            Text(
              'سجّل الدخول لعرض السلة',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'يجب تسجيل الدخول لإضافة دورات ومنتجات إلى السلة وإتمام الشراء',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
            ),
          ],
        ),
      ),
    );
  }
}

class _CartCourseTile extends StatelessWidget {
  const _CartCourseTile({
    required this.course,
    required this.imageUrl,
    required this.onTap,
    required this.onRemove,
  });

  final CartCourseItem course;
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
                      if (course.subscriptionType != null && course.subscriptionType != 'once') ...[
                        const SizedBox(height: 2),
                        Text(
                          course.subscriptionTypeLabel,
                          style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.primary.withValues(alpha: 0.9),
                          ),
                          textDirection: TextDirection.rtl,
                        ),
                      ],
                      const SizedBox(height: 6),
                      Text(
                        '${course.price} ر.س',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primary,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.remove_circle_outline_rounded, color: Colors.redAccent),
                  onPressed: onRemove,
                  tooltip: 'إزالة من السلة',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _CartProductTile extends StatelessWidget {
  const _CartProductTile({
    required this.item,
    required this.imageUrl,
    required this.onTap,
    required this.onRemove,
  });

  final CartProductItem item;
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
                        item.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppTheme.primaryDark,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textDirection: TextDirection.rtl,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${item.quantity} × ${item.unitPrice} ر.س',
                        style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${item.total} ر.س',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primary,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.remove_circle_outline_rounded, color: Colors.redAccent),
                  onPressed: onRemove,
                  tooltip: 'إزالة من السلة',
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  const _SummaryRow({required this.label, required this.value, this.valueStyle});

  final String label;
  final String value;
  final TextStyle? valueStyle;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        textDirection: TextDirection.rtl,
        children: [
          Text(label, style: TextStyle(fontSize: 14, color: Colors.grey.shade700)),
          Text(value, style: valueStyle ?? const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
        ],
      ),
    );
  }
}

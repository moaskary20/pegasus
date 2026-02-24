import 'package:flutter/material.dart';
import '../api/config.dart';
import '../api/store_api.dart';
import '../api/wishlist_api.dart';
import '../app_theme.dart';
import 'product_detail_screen.dart';

/// شاشة عرض منتجات تصنيف المتجر — تصميم احترافي مع حركات ظهور متتابعة
class StoreCategoryProductsScreen extends StatefulWidget {
  const StoreCategoryProductsScreen({
    super.key,
    required this.categoryId,
    required this.categoryName,
    this.subCategoryId,
  });

  final int categoryId;
  final String categoryName;
  final int? subCategoryId;

  @override
  State<StoreCategoryProductsScreen> createState() => _StoreCategoryProductsScreenState();
}

class _StoreCategoryProductsScreenState extends State<StoreCategoryProductsScreen> with SingleTickerProviderStateMixin {
  bool _loading = true;
  List<StoreProductItem> _products = [];
  final Set<int> _wishlistProductIds = {};
  late AnimationController _animController;
  static const int _staggerMs = 50;
  static const int _animDurationMs = 350;

  Future<void> _toggleWishlist(int productId) async {
    final isIn = _wishlistProductIds.contains(productId);
    final ok = isIn
        ? await WishlistApi.removeProduct(productId)
        : await WishlistApi.addProduct(productId);
    if (ok && mounted) {
      setState(() {
        if (isIn) _wishlistProductIds.remove(productId);
        else _wishlistProductIds.add(productId);
      });
    }
  }

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: _animDurationMs + (50 * 12)),
    );
    _load();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final results = await Future.wait([
      StoreApi.getProductsByCategory(
        categoryId: widget.categoryId,
        subCategoryId: widget.subCategoryId,
      ),
      WishlistApi.getWishlist(),
    ]);
    if (!mounted) return;
    final list = results[0] as List<StoreProductItem>;
    final wishlist = results[1] as WishlistResponse;
    setState(() {
      _products = list;
      _wishlistProductIds.clear();
      _wishlistProductIds.addAll(wishlist.productIds);
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
            else if (_products.isEmpty)
              SliverFillRemaining(child: _buildEmpty())
            else ...[
              SliverToBoxAdapter(child: _buildHeader()),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final product = _products[index];
                      return _AnimatedProductTile(
                        index: index,
                        product: product,
                        animation: _animController,
                        staggerMs: _staggerMs,
                        isInWishlist: _wishlistProductIds.contains(product.id),
                        onToggleWishlist: () => _toggleWishlist(product.id),
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) => ProductDetailScreen(
                                productSlug: product.slug,
                                productName: product.name,
                                initialIsInWishlist: _wishlistProductIds.contains(product.id),
                                productId: product.id,
                                onWishlistChanged: _load,
                              ),
                            ),
                          );
                        },
                      );
                    },
                    childCount: _products.length,
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
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
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
                Icon(Icons.shopping_bag_rounded, size: 20, color: AppTheme.primary),
                const SizedBox(width: 8),
                Text(
                  '${_products.length} منتج',
                  style: TextStyle(fontWeight: FontWeight.w600, color: AppTheme.primary, fontSize: 14),
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
          Icon(Icons.inventory_2_outlined, size: 72, color: AppTheme.primary.withValues(alpha: 0.4)),
          const SizedBox(height: 20),
          Text(
            'لا توجد منتجات في هذا التصنيف',
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

class _AnimatedProductTile extends StatelessWidget {
  const _AnimatedProductTile({
    required this.index,
    required this.product,
    required this.animation,
    required this.staggerMs,
    required this.onTap,
    required this.isInWishlist,
    required this.onToggleWishlist,
  });

  final int index;
  final StoreProductItem product;
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
              child: _ProductCard(
                product: product,
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

class _ProductCard extends StatelessWidget {
  const _ProductCard({
    required this.product,
    required this.onTap,
    required this.isInWishlist,
    required this.onToggleWishlist,
  });

  final StoreProductItem product;
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
                child: _image(product.mainImage, 120, 120),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF1A1A1A),
                              height: 1.3,
                            ),
                      ),
                      if (product.category != null) ...[
                        const SizedBox(height: 6),
                        Text(
                          product.category!.name,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        ),
                      ],
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Icon(Icons.star_rounded, size: 18, color: Colors.orange.shade700),
                          const SizedBox(width: 4),
                          Text(
                            '${product.averageRating}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1A1A1A),
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${product.ratingsCount} تقييم',
                            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Text(
                            '${product.price.toStringAsFixed(0)} ر.س',
                            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.primary,
                                ),
                          ),
                          if (product.hasDiscount && product.comparePrice != null) ...[
                            const SizedBox(width: 8),
                            Text(
                              '${product.comparePrice!.toStringAsFixed(0)} ر.س',
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

  Widget _image(String? url, double w, double h) {
    final fullUrl = _fullUrl(url);
    if (fullUrl == null || fullUrl.isEmpty) {
      return Container(
        width: w,
        height: h,
        color: Colors.grey.shade300,
        child: Icon(Icons.shopping_bag_rounded, size: 40, color: Colors.grey.shade500),
      );
    }
    return Image.network(
      fullUrl,
      width: w,
      height: h,
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Container(
        width: w,
        height: h,
        color: Colors.grey.shade300,
        child: Icon(Icons.shopping_bag_rounded, size: 40, color: Colors.grey.shade500),
      ),
    );
  }

  String? _fullUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    final u = url.trim();
    if (u.startsWith('http')) return u;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    if (u.startsWith('/')) return '$base$u';
    if (u.startsWith('storage/')) return '$base/$u';
    return '$base/storage/$u';
  }
}

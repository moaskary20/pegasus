import 'package:flutter/material.dart';
import '../api/auth_api.dart';
import '../api/config.dart';
import '../api/store_api.dart';
import '../api/wishlist_api.dart';
import '../api/cart_api.dart';
import '../app_theme.dart';
import 'cart_screen.dart';

/// شاشة تفاصيل المنتج — تعرض كل المزايا من الـ backend بتصميم احترافي وحركة
class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({
    super.key,
    required this.productSlug,
    this.productName,
    this.initialIsInWishlist = false,
    this.productId,
    this.onWishlistChanged,
  });

  final String productSlug;
  final String? productName;
  final bool initialIsInWishlist;
  final int? productId;
  final VoidCallback? onWishlistChanged;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> with SingleTickerProviderStateMixin {
  StoreProductDetail? _product;
  bool _loading = true;
  String? _error;
  int _selectedImageIndex = 0;
  bool _isInWishlist = false;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _isInWishlist = widget.initialIsInWishlist;
    _tabController = TabController(length: 3, vsync: this);
    _load();
  }

  Future<void> _toggleWishlist() async {
    final id = widget.productId ?? _product?.id;
    if (id == null) return;
    final wasInWishlist = _isInWishlist;
    final result = wasInWishlist
        ? await WishlistApi.removeProduct(id)
        : await WishlistApi.addProduct(id);
    if (result.isSuccess && mounted) {
      setState(() => _isInWishlist = !_isInWishlist);
      widget.onWishlistChanged?.call();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(wasInWishlist ? 'تمت الإزالة من المفضلة' : 'تم الإضافة في المفضلة'),
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
          : 'يجب تسجيل الدخول لإضافة المنتج إلى المفضلة';
    }
    if (result.isNotFound) return 'المنتج غير متوفر';
    if (result.isError) {
      final code = result.statusCode;
      return code != null ? 'حدث خطأ ($code)، حاول لاحقاً' : 'حدث خطأ، حاول لاحقاً';
    }
    return null;
  }

  Future<void> _addProductToCart(int productId) async {
    final ok = await CartApi.addProduct(productId);
    if (!mounted) return;
    if (ok) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('تمت إضافة المنتج إلى السلة'),
          action: SnackBarAction(
            label: 'فتح السلة',
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CartScreen())),
          ),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يجب تسجيل الدخول لإضافة المنتج إلى السلة')),
      );
    }
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
    final product = await StoreApi.getProductBySlug(widget.productSlug);
    if (!mounted) return;
    bool inWishlist = widget.initialIsInWishlist;
    if (product != null && widget.productId == null) {
      final res = await WishlistApi.getWishlist();
      inWishlist = res.productIds.contains(product.id);
    } else if (product != null && widget.productId != null) {
      inWishlist = widget.initialIsInWishlist;
    }
    setState(() {
      _product = product;
      _isInWishlist = inWishlist;
      _loading = false;
      _error = product == null ? 'لم يتم العثور على المنتج' : null;
    });
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
            widget.productName ?? 'تفاصيل المنتج',
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
          ),
        ),
        body: const Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }

    if (_error != null || _product == null) {
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

    final p = _product!;
    final images = p.images.isNotEmpty ? p.images : (p.mainImage != null ? [p.mainImage!] : <String>[]);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          _buildSliverAppBar(images),
          SliverToBoxAdapter(child: _buildInfoCard(p)),
          SliverPersistentHeader(
            pinned: true,
            delegate: _TabBarDelegate(
              tabController: _tabController,
              tabs: const ['الوصف', 'المواصفات', 'التقييمات'],
            ),
          ),
        ],
        body: TabBarView(
          controller: _tabController,
          children: [
            _DescriptionTab(product: p),
            _SpecsTab(product: p),
            _ReviewsTab(product: p),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomBar(p),
    );
  }

  Widget _buildSliverAppBar(List<String> images) {
    final url = images.isNotEmpty ? images[_selectedImageIndex.clamp(0, images.length - 1)] : null;
    return SliverAppBar(
      expandedHeight: 280,
      pinned: true,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Navigator.of(context).pop(),
      ),
      actions: [
        IconButton(
          icon: Icon(
            _isInWishlist ? Icons.favorite_rounded : Icons.favorite_border_rounded,
            color: _isInWishlist ? Colors.redAccent : Colors.white,
          ),
          onPressed: _toggleWishlist,
          tooltip: _isInWishlist ? 'إزالة من المفضلة' : 'إضافة للمفضلة',
        ),
      ],
      flexibleSpace: FlexibleSpaceBar(
        background: Stack(
          fit: StackFit.expand,
          children: [
            if (url != null && url.isNotEmpty)
              Image.network(
                _fullUrl(url) ?? url,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => _placeholder(),
              )
            else
              _placeholder(),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.transparent, Colors.black.withValues(alpha: 0.5)],
                ),
              ),
            ),
            if (images.length > 1)
              Positioned(
                left: 0,
                right: 0,
                bottom: 16,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(images.length.clamp(0, 8), (i) {
                    final selected = i == _selectedImageIndex;
                    return GestureDetector(
                      onTap: () => setState(() => _selectedImageIndex = i),
                      child: Container(
                        margin: const EdgeInsets.symmetric(horizontal: 3),
                        width: selected ? 24 : 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: selected ? Colors.white : Colors.white.withValues(alpha: 0.5),
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    );
                  }),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      color: AppTheme.primary,
      child: Icon(Icons.shopping_bag_rounded, size: 80, color: Colors.white.withValues(alpha: 0.5)),
    );
  }

  Widget _buildInfoCard(StoreProductDetail p) {
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
            Text(
              p.name,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF1A1A1A),
                    height: 1.3,
                  ),
            ),
            const SizedBox(height: 12),
            if (p.category != null) ...[
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF2563EB).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  p.category!.name,
                  style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF2563EB), fontSize: 13),
                ),
              ),
              const SizedBox(height: 12),
            ],
            Row(
              children: [
                Icon(Icons.star_rounded, size: 20, color: Colors.orange.shade700),
                const SizedBox(width: 4),
                Text('${p.averageRating}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                const SizedBox(width: 6),
                Text('${p.ratingsCount} تقييم', style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
                const Spacer(),
                if (p.hasDiscount && p.discountPercentage != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.red.shade50,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'خصم ${p.discountPercentage!.toInt()}%',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.red.shade700),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  '${p.price.toStringAsFixed(0)} ر.س',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary,
                      ),
                ),
                if (p.hasDiscount && p.comparePrice != null) ...[
                  const SizedBox(width: 8),
                  Text(
                    '${p.comparePrice!.toStringAsFixed(0)} ر.س',
                    style: TextStyle(fontSize: 14, color: Colors.grey.shade600, decoration: TextDecoration.lineThrough),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _Chip(icon: Icons.inventory_2_outlined, label: p.stockStatusLabel),
                const SizedBox(width: 12),
                _Chip(icon: Icons.sell_rounded, label: '${p.salesCount} مبيعة'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBottomBar(StoreProductDetail p) {
    return Container(
      padding: EdgeInsets.fromLTRB(20, 12, 20, 12 + MediaQuery.of(context).padding.bottom),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 12, offset: const Offset(0, -4))],
      ),
      child: SafeArea(
        child: p.isInStock
            ? FilledButton(
                onPressed: () => _addProductToCart(p.id),
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
              )
            : OutlinedButton(
                onPressed: null,
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: const Text('غير متوفر حالياً'),
              ),
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

class _Chip extends StatelessWidget {
  const _Chip({required this.icon, required this.label});
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

class _DescriptionTab extends StatefulWidget {
  const _DescriptionTab({required this.product});
  final StoreProductDetail product;

  @override
  State<_DescriptionTab> createState() => _DescriptionTabState();
}

class _DescriptionTabState extends State<_DescriptionTab> with SingleTickerProviderStateMixin {
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
    final p = widget.product;
    return FadeTransition(
      opacity: _fade,
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (p.shortDescription.isNotEmpty) ...[
              Text(
                'نبذة',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF1A1A1A),
                    ),
              ),
              const SizedBox(height: 8),
              Text(
                p.shortDescription,
                style: TextStyle(fontSize: 15, height: 1.6, color: Colors.grey.shade800),
              ),
              const SizedBox(height: 24),
            ],
            Text(
              'الوصف',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF1A1A1A),
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              p.description.isEmpty ? 'لا يوجد وصف متاح.' : p.description,
              style: TextStyle(fontSize: 15, height: 1.6, color: Colors.grey.shade800),
            ),
          ],
        ),
      ),
    );
  }
}

class _SpecsTab extends StatefulWidget {
  const _SpecsTab({required this.product});
  final StoreProductDetail product;

  @override
  State<_SpecsTab> createState() => _SpecsTabState();
}

class _SpecsTabState extends State<_SpecsTab> with SingleTickerProviderStateMixin {
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
    final p = widget.product;
    final items = <_SpecRow>[
      _SpecRow('حالة التوفر', p.stockStatusLabel),
      if (p.sku != null) _SpecRow('رمز المنتج (SKU)', p.sku!),
      if (p.weight != null) _SpecRow('الوزن', '${p.weight} كغ'),
      if (p.dimensions != null && p.dimensions!.isNotEmpty) _SpecRow('الأبعاد', p.dimensions!),
      _SpecRow('نوع المنتج', p.isDigital ? 'منتج رقمي' : 'منتج مادي'),
      _SpecRow('يتطلب شحن', p.requiresShipping ? 'نعم' : 'لا'),
    ];
    return FadeTransition(
      opacity: _fade,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
        itemCount: items.length,
        itemBuilder: (context, i) {
          final row = items[i];
          return Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 120,
                  child: Text(
                    row.label,
                    style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
                  ),
                ),
                Expanded(
                  child: Text(
                    row.value,
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _SpecRow {
  _SpecRow(this.label, this.value);
  final String label;
  final String value;
}

class _ReviewsTab extends StatelessWidget {
  const _ReviewsTab({required this.product});
  final StoreProductDetail product;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.star_rounded, size: 64, color: Colors.orange.shade300),
          const SizedBox(height: 16),
          Text(
            '${product.averageRating} من 5',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text('${product.ratingsCount} تقييم', style: TextStyle(color: Colors.grey.shade600)),
          const SizedBox(height: 24),
          Text(
            'قائمة التقييمات ستُعرض هنا عند ربطها بالـ API',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }
}

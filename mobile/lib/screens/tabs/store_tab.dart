import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../../widgets/skeleton_loading.dart';
import '../store_category_products_screen.dart';
import '../../api/store_api.dart';
import '../../app_theme.dart';

/// تبويب الاستور: يعرض تصنيفات المتجر من إدارة المتجر (ProductCategory)
class StoreTab extends StatefulWidget {
  const StoreTab({super.key, this.onOpenDrawer, this.wishlistCount = 0, this.onWishlistCountChanged, this.onOpenFavorite, this.cartCount = 0, this.notificationsAndRemindersCount = 0, this.messagesCount = 0, this.onOpenCart, this.onOpenNotificationsAndReminders, this.onOpenMessages});

  final VoidCallback? onOpenDrawer;
  final int wishlistCount;
  final void Function(int delta)? onWishlistCountChanged;
  final VoidCallback? onOpenFavorite;
  final int cartCount;
  final int notificationsAndRemindersCount;
  final int messagesCount;
  final VoidCallback? onOpenCart;
  final VoidCallback? onOpenNotificationsAndReminders;
  final VoidCallback? onOpenMessages;

  @override
  State<StoreTab> createState() => _StoreTabState();
}

class _StoreTabState extends State<StoreTab> {
  bool _loading = true;
  List<StoreCategory> _categories = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await StoreApi.getCategories();
    if (!mounted) return;
    setState(() {
      _categories = res.categories;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F8F8),
      appBar: AppHeader(
        title: 'الاستور',
        onMenu: widget.onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
        favoriteCount: widget.wishlistCount,
        onFavorite: widget.onOpenFavorite,
        cartCount: widget.cartCount,
        notificationsAndRemindersCount: widget.notificationsAndRemindersCount,
        messagesCount: widget.messagesCount,
        onCart: widget.onOpenCart,
        onNotificationsAndReminders: widget.onOpenNotificationsAndReminders,
        onMessages: widget.onOpenMessages,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const SkeletonCategoriesPage()
            : _categories.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.category_outlined, size: 64, color: AppTheme.primary.withValues(alpha: 0.5)),
                        const SizedBox(height: 16),
                        Text(
                          'لا توجد تصنيفات حالياً',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                  )
                : CustomScrollView(
                    slivers: [
                      SliverPadding(
                        padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                        sliver: SliverToBoxAdapter(
                          child: Text(
                            'جميع التصنيفات',
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF333333),
                                ),
                          ),
                        ),
                      ),
                      SliverPadding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        sliver: SliverGrid(
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            mainAxisSpacing: 12,
                            crossAxisSpacing: 12,
                            childAspectRatio: 0.85,
                          ),
                          delegate: SliverChildBuilderDelegate(
                            (context, index) {
                              final cat = _categories[index];
                              return _StoreCategoryCard(
                                category: cat,
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute<void>(
                                      builder: (_) => StoreCategoryProductsScreen(
                                        categoryId: cat.id,
                                        categoryName: cat.name,
                                      ),
                                    ),
                                  );
                                },
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
}

class _StoreCategoryCard extends StatelessWidget {
  const _StoreCategoryCard({required this.category, required this.onTap});

  final StoreCategory category;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = _categoryColor(category.name);
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 0,
      shadowColor: Colors.black.withValues(alpha: 0.06),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (category.image != null && category.image!.isNotEmpty)
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    category.image!,
                    width: 56,
                    height: 56,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _iconPlaceholder(color),
                  ),
                )
              else
                _iconPlaceholder(color),
              const SizedBox(height: 12),
              Text(
                category.name,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF333333),
                    ),
              ),
              const SizedBox(height: 4),
              Text(
                '${category.productsCount} منتج',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.grey.shade600,
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _iconPlaceholder(Color color) {
    return Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        shape: BoxShape.circle,
      ),
      child: Icon(Icons.storefront_rounded, size: 32, color: color),
    );
  }

  Color _categoryColor(String name) {
    final hash = name.hashCode.abs();
    const colors = [
      Color(0xFF2c004d),
      Color(0xFF7c3aed),
      Color(0xFF2563eb),
      Color(0xFF059669),
      Color(0xFFdc2626),
      Color(0xFFea580c),
    ];
    return colors[hash % colors.length];
  }
}

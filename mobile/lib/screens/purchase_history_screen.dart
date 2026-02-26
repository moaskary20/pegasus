import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/orders_api.dart';
import '../api/store_orders_api.dart';

/// عنصر موحد للعرض (دورات أو منتجات)
class _PurchaseItem {
  _PurchaseItem({required this.id, required this.orderNumber, required this.total, required this.status, required this.itemsSummary, required this.isStoreOrder, this.createdAt});
  final int id;
  final String orderNumber;
  final double total;
  final String status;
  final List<_ItemSummary> itemsSummary;
  final bool isStoreOrder;
  final String? createdAt;
}

class _ItemSummary {
  _ItemSummary({required this.title, required this.quantity, required this.price});
  final String title;
  final int quantity;
  final double price;
}

/// سجل المشتريات — دورات + طلبات المتجر (GET /api/orders, GET /api/store-orders)
class PurchaseHistoryScreen extends StatefulWidget {
  const PurchaseHistoryScreen({super.key});

  @override
  State<PurchaseHistoryScreen> createState() => _PurchaseHistoryScreenState();
}

class _PurchaseHistoryScreenState extends State<PurchaseHistoryScreen> {
  bool _loading = true;
  List<_PurchaseItem> _list = [];
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final ordersRes = await OrdersApi.getOrders();
    final storeRes = await StoreOrdersApi.getStoreOrders();
    if (!mounted) return;
    final combined = <_PurchaseItem>[];
    for (final o in ordersRes.orders) {
      combined.add(_PurchaseItem(
        id: o.id,
        orderNumber: o.orderNumber,
        total: o.total,
        status: o.status,
        itemsSummary: o.itemsSummary.map((e) => _ItemSummary(title: e.title, quantity: e.quantity, price: e.price)).toList(),
        isStoreOrder: false,
        createdAt: o.createdAt,
      ));
    }
    for (final o in storeRes.orders) {
      combined.add(_PurchaseItem(
        id: o.id,
        orderNumber: o.orderNumber,
        total: o.total,
        status: o.paymentStatus == 'paid' ? 'paid' : o.status,
        itemsSummary: o.itemsSummary.map((e) => _ItemSummary(title: e.productName, quantity: e.quantity, price: e.price)).toList(),
        isStoreOrder: true,
        createdAt: o.createdAt,
      ));
    }
    combined.sort((a, b) => (b.createdAt ?? '').compareTo(a.createdAt ?? ''));
    setState(() {
      _list = combined;
      _needsAuth = ordersRes.needsAuth || storeRes.needsAuth;
      _loading = false;
    });
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'paid':
        return 'مدفوع';
      case 'pending':
        return 'قيد الانتظار';
      case 'failed':
        return 'فشل';
      case 'processing':
        return 'قيد المعالجة';
      case 'shipped':
        return 'تم الشحن';
      case 'delivered':
        return 'تم التسليم';
      case 'cancelled':
        return 'ملغي';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surface,
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.maybePop(context),
        ),
        title: const Text('سجل المشتريات'),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _needsAuth
                ? SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: ConstrainedBox(
                      constraints: BoxConstraints(minHeight: MediaQuery.of(context).size.height - 150),
                      child: _NeedsAuth(message: 'سجّل الدخول لعرض سجل المشتريات'),
                    ),
                  )
                : _list.isEmpty
                    ? SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: ConstrainedBox(
                          constraints: BoxConstraints(minHeight: MediaQuery.of(context).size.height - 150),
                          child: _EmptyState(
                            message: 'سجل المشتريات',
                            subtitle: 'ستظهر هنا فواتير الطلبات والدفعات',
                            onRefresh: _load,
                          ),
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                        itemCount: _list.length,
                        itemBuilder: (_, i) {
                          final item = _list[i];
                          return _OrderCard(
                            item: item,
                            statusLabel: _statusLabel(item.status),
                          );
                        },
                      ),
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.item, required this.statusLabel});

  final _PurchaseItem item;
  final String statusLabel;

  @override
  Widget build(BuildContext context) {
    final isPaid = item.status == 'paid';
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Row(
                    children: [
                      Text(
                        item.orderNumber,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primaryDark,
                            ),
                      ),
                      if (item.isStoreOrder) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(color: AppTheme.primary.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(6)),
                          child: Text('متجر', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppTheme.primary)),
                        ),
                      ],
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isPaid ? Colors.green.withValues(alpha: 0.15) : Colors.orange.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(statusLabel, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: isPaid ? Colors.green : Colors.orange)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ...item.itemsSummary.take(3).map((e) => Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text('• ${e.title}', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade700), maxLines: 1, overflow: TextOverflow.ellipsis),
                )),
            if (item.itemsSummary.length > 3)
              Text('+ ${item.itemsSummary.length - 3} عناصر أخرى', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade500)),
            const Divider(height: 16),
            Text(
              'الإجمالي: ${item.total.toStringAsFixed(1)} ر.س',
              style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: AppTheme.primary),
            ),
          ],
        ),
      ),
    );
  }
}

class _NeedsAuth extends StatelessWidget {
  const _NeedsAuth({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.login_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 16),
          Text(message, textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppTheme.primaryDark)),
        ],
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.message, this.subtitle, required this.onRefresh});

  final String message;
  final String? subtitle;
  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.receipt_long_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
          const SizedBox(height: 16),
          Text(message, textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleMedium?.copyWith(color: AppTheme.primaryDark)),
          if (subtitle != null) ...[
            const SizedBox(height: 8),
            Text(subtitle!, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600)),
          ],
          const SizedBox(height: 16),
          TextButton.icon(onPressed: onRefresh, icon: const Icon(Icons.refresh_rounded), label: const Text('تحديث')),
        ],
      ),
    );
  }
}

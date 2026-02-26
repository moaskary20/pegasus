import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/orders_api.dart';
import 'feature_scaffold.dart';

/// سجل المشتريات — بيانات من الـ backend (GET /api/orders)
class PurchaseHistoryScreen extends StatefulWidget {
  const PurchaseHistoryScreen({super.key});

  @override
  State<PurchaseHistoryScreen> createState() => _PurchaseHistoryScreenState();
}

class _PurchaseHistoryScreenState extends State<PurchaseHistoryScreen> {
  bool _loading = true;
  List<OrderItem> _list = [];
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await OrdersApi.getOrders();
    if (!mounted) return;
    setState(() {
      _list = res.orders;
      _needsAuth = res.needsAuth;
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
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'سجل المشتريات',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _needsAuth
                ? _NeedsAuth(message: 'سجّل الدخول لعرض سجل المشتريات')
                : _list.isEmpty
                    ? _EmptyState(
                        message: 'سجل المشتريات',
                        subtitle: 'ستظهر هنا فواتير الطلبات والدفعات',
                        onRefresh: _load,
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                        itemCount: _list.length,
                        itemBuilder: (_, i) {
                          final order = _list[i];
                          return _OrderCard(
                            order: order,
                            statusLabel: _statusLabel(order.status),
                          );
                        },
                      ),
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order, required this.statusLabel});

  final OrderItem order;
  final String statusLabel;

  @override
  Widget build(BuildContext context) {
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
                Text(
                  order.orderNumber,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: order.status == 'paid' ? Colors.green.withValues(alpha: 0.15) : Colors.orange.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(statusLabel, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: order.status == 'paid' ? Colors.green : Colors.orange)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ...order.itemsSummary.take(3).map((e) => Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text('• ${e.title}', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade700), maxLines: 1, overflow: TextOverflow.ellipsis),
                )),
            if (order.itemsSummary.length > 3)
              Text('+ ${order.itemsSummary.length - 3} عناصر أخرى', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade500)),
            const Divider(height: 16),
            Text(
              'الإجمالي: ${order.total.toStringAsFixed(1)} ر.س',
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

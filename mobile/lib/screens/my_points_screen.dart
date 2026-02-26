import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/points_api.dart';
import 'feature_scaffold.dart';

/// شاشة نقاطي — الرصيد، الرتبة، سجل المعاملات، المكافآت
class MyPointsScreen extends StatefulWidget {
  const MyPointsScreen({super.key});

  @override
  State<MyPointsScreen> createState() => _MyPointsScreenState();
}

class _MyPointsScreenState extends State<MyPointsScreen> with SingleTickerProviderStateMixin {
  PointsSummary? _summary;
  List<PointTransactionItem> _transactions = [];
  List<RewardItem> _rewards = [];
  bool _loading = true;
  bool _loadingTransactions = false;
  int _transactionsPage = 1;
  bool _hasMoreTransactions = true;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadAll();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAll() async {
    setState(() => _loading = true);
    final summary = await PointsApi.getSummary();
    final rewards = await PointsApi.getRewards();
    final txRes = await PointsApi.getTransactions(page: 1);
    if (mounted) {
      setState(() {
        _summary = summary;
        _rewards = rewards.rewards;
        _transactions = txRes.transactions;
        _transactionsPage = 1;
        _hasMoreTransactions = txRes.currentPage < txRes.lastPage;
        _loading = false;
      });
    }
  }

  Future<void> _loadMoreTransactions() async {
    if (_loadingTransactions || !_hasMoreTransactions) return;
    setState(() => _loadingTransactions = true);
    final nextPage = _transactionsPage + 1;
    final txRes = await PointsApi.getTransactions(page: nextPage);
    if (mounted) {
      setState(() {
        _transactions.addAll(txRes.transactions);
        _transactionsPage = nextPage;
        _hasMoreTransactions = txRes.currentPage < txRes.lastPage;
        _loadingTransactions = false;
      });
    }
  }

  Future<void> _redeemReward(RewardItem reward) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('استبدال المكافأة'),
        content: Text(
          'هل تريد استبدال "${reward.name}" مقابل ${reward.pointsRequired} نقطة؟',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('إلغاء'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
            child: const Text('نعم، استبدال'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    final result = await PointsApi.redeemReward(reward.id);
    if (!mounted) return;
    if (result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.code != null ? 'تم الاستبدال! استخدم الكود: ${result.code}' : result.message),
          backgroundColor: Colors.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
      _loadAll();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message),
          backgroundColor: AppTheme.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'نقاطي',
      body: RefreshIndicator(
        onRefresh: _loadAll,
        color: AppTheme.primary,
        child: _loading
            ? const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child: CircularProgressIndicator(color: AppTheme.primary),
                ),
              )
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _PointsSummaryCard(summary: _summary),
                    const SizedBox(height: 24),
                    TabBar(
                      controller: _tabController,
                      indicatorColor: AppTheme.primary,
                      labelColor: AppTheme.primary,
                      tabs: const [
                        Tab(text: 'المكافآت'),
                        Tab(text: 'سجل المعاملات'),
                      ],
                    ),
                    SizedBox(
                      height: 400,
                      child: TabBarView(
                        controller: _tabController,
                        children: [
                          _RewardsTab(
                            rewards: _rewards,
                            availablePoints: _summary?.availablePoints ?? 0,
                            onRedeem: _redeemReward,
                            onRefresh: _loadAll,
                          ),
                          _TransactionsTab(
                            transactions: _transactions,
                            hasMore: _hasMoreTransactions,
                            loadingMore: _loadingTransactions,
                            onLoadMore: _loadMoreTransactions,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}

class _PointsSummaryCard extends StatelessWidget {
  const _PointsSummaryCard({this.summary});

  final PointsSummary? summary;

  Color get _rankColor {
    if (summary?.rankColor == null) return const Color(0xFFcd7f32);
    try {
      return Color(int.parse(summary!.rankColor!.replaceFirst('#', '0xFF')));
    } catch (_) {
      return const Color(0xFFcd7f32);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (summary == null) {
      return const SizedBox.shrink();
    }
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primary, AppTheme.primaryLight],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(
            '${summary!.availablePoints}',
            style: const TextStyle(
              fontSize: 42,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'نقطة متاحة',
            style: TextStyle(
              fontSize: 16,
              color: Colors.white.withValues(alpha: 0.9),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.workspace_premium_rounded, color: _rankColor, size: 22),
                    const SizedBox(width: 8),
                    Text(
                      summary!.rankLabel,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
              if (summary!.rankPosition != null) ...[
                const SizedBox(width: 12),
                Text(
                  'المركز #${summary!.rankPosition}',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.white.withValues(alpha: 0.85),
                  ),
                ),
              ],
            ],
          ),
          if (summary!.pointsForNextRank != null && summary!.pointsForNextRank! > 0) ...[
            const SizedBox(height: 12),
            Text(
              'النقاط للرتبة التالية: ${summary!.pointsForNextRank}',
              style: TextStyle(
                fontSize: 13,
                color: Colors.white.withValues(alpha: 0.8),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _RewardsTab extends StatelessWidget {
  const _RewardsTab({
    required this.rewards,
    required this.availablePoints,
    required this.onRedeem,
    required this.onRefresh,
  });

  final List<RewardItem> rewards;
  final int availablePoints;
  final void Function(RewardItem) onRedeem;
  final VoidCallback onRefresh;

  @override
  Widget build(BuildContext context) {
    if (rewards.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.card_giftcard_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'لا توجد مكافآت متاحة حالياً',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.only(top: 12),
      itemCount: rewards.length,
      itemBuilder: (context, i) {
        final r = rewards[i];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                if (r.image != null && r.image!.isNotEmpty)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: Image.network(
                      r.image!,
                      width: 56,
                      height: 56,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _rewardIcon(r.type),
                    ),
                  )
                else
                  _rewardIcon(r.type),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        r.name,
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primaryDark,
                            ),
                      ),
                      if (r.description != null && r.description!.isNotEmpty)
                        Text(
                          r.description!,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.star_rounded, size: 18, color: Colors.amber.shade700),
                          const SizedBox(width: 4),
                          Text(
                            '${r.pointsRequired} نقطة',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Colors.amber.shade800,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                if (r.canRedeem)
                  FilledButton(
                    onPressed: () => onRedeem(r),
                    style: FilledButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                    child: const Text('استبدال'),
                  )
                else
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${r.pointsRequired - availablePoints} نقطة متبقية',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _rewardIcon(String type) {
    IconData icon = Icons.card_giftcard_rounded;
    if (type == 'discount') icon = Icons.percent_rounded;
    if (type == 'free_course') icon = Icons.school_rounded;
    if (type == 'badge') icon = Icons.workspace_premium_rounded;
    if (type == 'certificate') icon = Icons.workspace_premium_rounded;
    return Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Icon(icon, color: AppTheme.primary, size: 28),
    );
  }
}

class _TransactionsTab extends StatelessWidget {
  const _TransactionsTab({
    required this.transactions,
    required this.hasMore,
    required this.loadingMore,
    required this.onLoadMore,
  });

  final List<PointTransactionItem> transactions;
  final bool hasMore;
  final bool loadingMore;
  final VoidCallback onLoadMore;

  @override
  Widget build(BuildContext context) {
    if (transactions.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.receipt_long_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'لا توجد معاملات بعد',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.only(top: 12),
      itemCount: transactions.length + (hasMore ? 1 : 0),
      itemBuilder: (context, i) {
        if (i == transactions.length) {
          if (loadingMore) {
            return const Padding(
              padding: EdgeInsets.all(16),
              child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
            );
          }
          return Padding(
            padding: const EdgeInsets.all(16),
            child: Center(
              child: TextButton(
                onPressed: onLoadMore,
                child: const Text('تحميل المزيد'),
              ),
            ),
          );
        }
        final t = transactions[i];
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: t.points >= 0
                  ? Colors.green.withValues(alpha: 0.15)
                  : Colors.red.withValues(alpha: 0.15),
              child: Icon(
                t.points >= 0 ? Icons.add_rounded : Icons.remove_rounded,
                color: t.points >= 0 ? Colors.green : Colors.red,
                size: 22,
              ),
            ),
            title: Text(
              t.description,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primaryDark,
                  ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            subtitle: Text(
              t.typeLabel,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
            ),
            trailing: Text(
              '${t.points >= 0 ? '+' : ''}${t.points}',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: t.points >= 0 ? Colors.green : Colors.red,
              ),
            ),
          ),
        );
      },
    );
  }
}

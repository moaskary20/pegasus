import 'package:flutter/material.dart';
import '../app_theme.dart';
import '../api/my_assignments_api.dart';
import 'feature_scaffold.dart';

/// واجباتي — بيانات من الـ backend (GET /api/my-assignments)
class MyAssignmentsScreen extends StatefulWidget {
  const MyAssignmentsScreen({super.key});

  @override
  State<MyAssignmentsScreen> createState() => _MyAssignmentsScreenState();
}

class _MyAssignmentsScreenState extends State<MyAssignmentsScreen> {
  bool _loading = true;
  List<MyAssignmentItem> _list = [];
  int _pending = 0;
  int _submitted = 0;
  int _graded = 0;
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await MyAssignmentsApi.getMyAssignments();
    if (!mounted) return;
    setState(() {
      _list = res.assignments;
      _pending = res.pending;
      _submitted = res.submitted;
      _graded = res.graded;
      _needsAuth = res.needsAuth;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'واجباتي',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _needsAuth
                ? _NeedsAuth(message: 'سجّل الدخول لعرض الواجبات')
                : _list.isEmpty
                    ? _EmptyState(
                        message: 'الواجبات والاختبارات ستظهر هنا',
                        subtitle: 'بعد إكمال الدروس ستجد الواجبات المطلوبة هنا',
                        onRefresh: _load,
                      )
                    : CustomScrollView(
                        slivers: [
                          SliverToBoxAdapter(
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
                              child: Row(
                                children: [
                                  _StatChip(label: 'قيد الانتظار', value: '$_pending', color: Colors.orange),
                                  const SizedBox(width: 8),
                                  _StatChip(label: 'مُرسل', value: '$_submitted', color: Colors.blue),
                                  const SizedBox(width: 8),
                                  _StatChip(label: 'مقيّم', value: '$_graded', color: Colors.green),
                                ],
                              ),
                            ),
                          ),
                          SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (_, i) {
                                final a = _list[i];
                                return _AssignmentTile(assignment: a);
                              },
                              childCount: _list.length,
                            ),
                          ),
                          const SliverToBoxAdapter(child: SizedBox(height: 24)),
                        ],
                      ),
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.label, required this.value, required this.color});

  final String label;
  final String value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text('$label: $value', style: Theme.of(context).textTheme.labelMedium?.copyWith(fontWeight: FontWeight.w600, color: color)),
    );
  }
}

class _AssignmentTile extends StatelessWidget {
  const _AssignmentTile({required this.assignment});

  final MyAssignmentItem assignment;

  @override
  Widget build(BuildContext context) {
    final statusLabel = assignment.status == 'graded'
        ? 'مقيّم'
        : assignment.status == 'submitted'
            ? 'مُرسل'
            : assignment.status == 'resubmit_requested'
                ? 'إعادة تقديم'
                : 'قيد الانتظار';
    final statusColor = assignment.status == 'graded'
        ? Colors.green
        : assignment.status == 'submitted'
            ? Colors.blue
            : Colors.orange;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 0,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      assignment.title,
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryDark,
                          ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(statusLabel, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: statusColor)),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                assignment.courseTitle,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
              ),
              if (assignment.score != null) ...[
                const SizedBox(height: 4),
                Text('الدرجة: ${assignment.score}', style: Theme.of(context).textTheme.labelMedium?.copyWith(color: AppTheme.primary)),
              ],
            ],
          ),
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
          Icon(Icons.assignment_outlined, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
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

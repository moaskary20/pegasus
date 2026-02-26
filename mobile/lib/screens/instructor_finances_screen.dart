import 'package:flutter/material.dart';
import '../api/instructor_finances_api.dart';
import '../api/auth_api.dart';
import '../app_theme.dart';

const Color _primary = Color(0xFF2c004d);

/// الإدارة المالية للمدرس — إجمالي الأرباح، الرصيد، أرباح الدورات
class InstructorFinancesScreen extends StatefulWidget {
  const InstructorFinancesScreen({super.key});

  @override
  State<InstructorFinancesScreen> createState() => _InstructorFinancesScreenState();
}

class _InstructorFinancesScreenState extends State<InstructorFinancesScreen> {
  bool _loading = true;
  String? _error;
  InstructorFinancesResponse? _data;
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _needsAuth = false;
    });
    await AuthApi.loadStoredToken();
    final result = await InstructorFinancesApi.getFinancesWithError();
    if (!mounted) return;
    if (result.data != null) {
      setState(() {
        _data = result.data;
        _loading = false;
      });
      return;
    }
    if (AuthApi.token == null) {
      setState(() {
        _loading = false;
        _needsAuth = true;
      });
    } else {
      setState(() {
        _loading = false;
        _error = result.errorMessage ?? 'تعذر تحميل البيانات';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      appBar: AppBar(
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.maybePop(context),
        ),
        title: const Text('الإدارة المالية', style: TextStyle(fontWeight: FontWeight.w600)),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: _primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: _primary))
            : _needsAuth
                ? _buildNeedsAuth()
                : _error != null
                    ? _buildError()
                    : _buildContent(),
      ),
    );
  }

  Widget _buildNeedsAuth() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: ConstrainedBox(
        constraints: BoxConstraints(minHeight: MediaQuery.of(context).size.height - 200),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const SizedBox(height: 48),
              Icon(Icons.lock_outline_rounded, size: 72, color: _primary.withValues(alpha: 0.5)),
              const SizedBox(height: 24),
              Text(
                'سجّل الدخول كمدرب لعرض الإدارة المالية',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade700),
                textDirection: TextDirection.rtl,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildError() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: ConstrainedBox(
        constraints: BoxConstraints(minHeight: MediaQuery.of(context).size.height - 200),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const SizedBox(height: 48),
              Icon(Icons.error_outline_rounded, size: 72, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(
                _error ?? 'حدث خطأ',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade700),
                textDirection: TextDirection.rtl,
              ),
              const SizedBox(height: 24),
              FilledButton.icon(
                onPressed: _load,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('إعادة المحاولة'),
                style: FilledButton.styleFrom(backgroundColor: _primary),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    final s = _data!.stats;
    final courses = _data!.courses;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: LinearGradient(colors: [_primary, _primary.withValues(alpha: 0.85)], begin: Alignment.topRight, end: Alignment.bottomLeft),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [BoxShadow(color: _primary.withValues(alpha: 0.3), blurRadius: 12, offset: const Offset(0, 4))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('الرصيد المتاح للسحب', style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 13)),
              const SizedBox(height: 8),
              Text(
                '${s.availableBalance.toStringAsFixed(2)} ج.م',
                style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(child: _StatChip(label: 'إجمالي الأرباح', value: '${s.totalEarnings.toStringAsFixed(0)} ج.م')),
                  Expanded(child: _StatChip(label: 'قيد السحب', value: '${s.pendingPayout.toStringAsFixed(0)} ج.م')),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _StatChip(label: 'تم سحبه', value: '${s.paidOut.toStringAsFixed(0)} ج.م')),
                  Expanded(child: _StatChip(label: 'العمولة', value: '${s.commissionRate.toStringAsFixed(0)}%')),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 24),
        Text(
          'أرباح الدورات',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold, color: _primary),
          textDirection: TextDirection.rtl,
        ),
        const SizedBox(height: 12),
        if (courses.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(16)),
            child: Column(
              children: [
                Icon(Icons.school_outlined, size: 56, color: Colors.grey.shade400),
                const SizedBox(height: 16),
                Text(
                  'لا توجد دورات',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade700),
                  textDirection: TextDirection.rtl,
                ),
                const SizedBox(height: 8),
                Text(
                  'قم بإنشاء دورات وبيعها لبدء تحقيق الأرباح',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                  textAlign: TextAlign.center,
                  textDirection: TextDirection.rtl,
                ),
              ],
            ),
          )
        else
          ...courses.map((c) => _CourseEarningCard(course: c)),
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
        const SizedBox(height: 2),
        Text(label, style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 11)),
      ],
    );
  }
}

class _CourseEarningCard extends StatelessWidget {
  const _CourseEarningCard({required this.course});

  final CourseEarningItem course;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Row(
        textDirection: TextDirection.rtl,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: _primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
            child: Icon(Icons.menu_book_rounded, color: _primary, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  course.title,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: _primary),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textDirection: TextDirection.rtl,
                ),
                const SizedBox(height: 4),
                Text(
                  '${course.students} طالب • المبيعات: ${course.totalSales.toStringAsFixed(0)} ج.م • ${course.commissionRate.toStringAsFixed(0)}%',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  textDirection: TextDirection.rtl,
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${course.commissionAmount.toStringAsFixed(2)} ج.م',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                textDirection: TextDirection.ltr,
              ),
              Text('أرباحك', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
            ],
          ),
        ],
      ),
    );
  }
}

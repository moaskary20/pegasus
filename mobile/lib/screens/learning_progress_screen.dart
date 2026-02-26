import 'package:flutter/material.dart';
import '../api/auth_api.dart';
import '../api/my_courses_api.dart';
import '../app_theme.dart';

/// شاشة تتبع التقدم — إحصائيات التعلم (ساعات، دروس مكتملة، إنجازات)
class LearningProgressScreen extends StatefulWidget {
  const LearningProgressScreen({super.key});

  @override
  State<LearningProgressScreen> createState() => _LearningProgressScreenState();
}

class _LearningProgressScreenState extends State<LearningProgressScreen> {
  bool _loading = true;
  MyCoursesResponse? _data;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    await AuthApi.loadStoredToken();
    if (AuthApi.token == null) {
      if (mounted) setState(() => _loading = false);
      return;
    }
    setState(() => _loading = true);
    final res = await MyCoursesApi.getMyCourses();
    if (mounted) setState(() {
      _data = res;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F3F8),
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        title: const Text('تتبع التقدم', style: TextStyle(fontWeight: FontWeight.w600)),
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : AuthApi.token == null
                ? _buildLoginPrompt()
                : _buildContent(),
      ),
    );
  }

  Widget _buildLoginPrompt() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.school_rounded, size: 80, color: AppTheme.primary.withValues(alpha: 0.5)),
            const SizedBox(height: 16),
            Text(
              'سجّل الدخول لمشاهدة إحصائياتك',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade700),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final d = _data;
    if (d == null) return const SizedBox.shrink();

    final totalHours = d.totalHours;
    final completedCount = d.completedCount;
    final inProgressCount = d.inProgressCount;
    final avgProgress = d.avgProgress;
    final totalCourses = d.totalCourses;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        children: [
          _buildStatsHeader(avgProgress),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'إحصائيات التعلم',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                  textDirection: TextDirection.rtl,
                ),
                const SizedBox(height: 16),
                _StatCard(
                  icon: Icons.play_circle_filled_rounded,
                  iconColor: Colors.blue,
                  title: 'ساعات التعلم',
                  value: totalHours.toStringAsFixed(1),
                  unit: 'ساعة',
                ),
                _StatCard(
                  icon: Icons.check_circle_rounded,
                  iconColor: Colors.green,
                  title: 'دورات مكتملة',
                  value: completedCount.toString(),
                  unit: 'دورة',
                ),
                _StatCard(
                  icon: Icons.pending_rounded,
                  iconColor: Colors.orange,
                  title: 'قيد التقدم',
                  value: inProgressCount.toString(),
                  unit: 'دورة',
                ),
                _StatCard(
                  icon: Icons.menu_book_rounded,
                  iconColor: AppTheme.primary,
                  title: 'إجمالي الدورات',
                  value: totalCourses.toString(),
                  unit: 'دورة',
                ),
                const SizedBox(height: 24),
                _buildAchievements(completedCount, totalHours),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsHeader(double avgProgress) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primary,
            AppTheme.primaryLight,
          ],
        ),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          children: [
            Text(
              'متوسط تقدمك',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.9),
                fontSize: 16,
              ),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 8),
            Text(
              '${avgProgress.toStringAsFixed(0)}%',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 42,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: LinearProgressIndicator(
                value: (avgProgress / 100).clamp(0.0, 1.0),
                minHeight: 8,
                backgroundColor: Colors.white.withValues(alpha: 0.3),
                valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAchievements(int completedCount, double totalHours) {
    final badges = <_Badge>[];
    if (completedCount >= 1) {
      badges.add(_Badge(emoji: '🎓', title: 'أول دورة', desc: 'أكملت أول دورة'));
    }
    if (completedCount >= 3) {
      badges.add(_Badge(emoji: '🌟', title: 'متعلّم نشط', desc: 'أكملت 3 دورات'));
    }
    if (completedCount >= 5) {
      badges.add(_Badge(emoji: '🏆', title: 'خبير', desc: 'أكملت 5 دورات'));
    }
    if (totalHours >= 10) {
      badges.add(_Badge(emoji: '⏱️', title: '10 ساعات', desc: 'أكثر من 10 ساعات تعلم'));
    }
    if (totalHours >= 50) {
      badges.add(_Badge(emoji: '💪', title: 'ملتزم', desc: '50+ ساعة تعلم'));
    }
    if (badges.isEmpty) {
      badges.add(_Badge(emoji: '🚀', title: 'ابدأ رحلتك', desc: 'أكمل دروسك الأولى لفتح الإنجازات'));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'الإنجازات',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryDark,
              ),
          textDirection: TextDirection.rtl,
        ),
        const SizedBox(height: 12),
        ...badges.map((b) => _AchievementChip(
              emoji: b.emoji,
              title: b.title,
              desc: b.desc,
            )),
      ],
    );
  }
}

class _Badge {
  _Badge({required this.emoji, required this.title, required this.desc});
  final String emoji, title, desc;
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.value,
    required this.unit,
  });

  final IconData icon;
  final Color iconColor;
  final String title;
  final String value;
  final String unit;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          textDirection: TextDirection.rtl,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: iconColor, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey.shade600,
                        ),
                    textDirection: TextDirection.rtl,
                  ),
                  Text(
                    '$value $unit',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryDark,
                        ),
                    textDirection: TextDirection.rtl,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AchievementChip extends StatelessWidget {
  const _AchievementChip({
    required this.emoji,
    required this.title,
    required this.desc,
  });

  final String emoji;
  final String title;
  final String desc;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        textDirection: TextDirection.rtl,
        children: [
          Text(emoji, style: const TextStyle(fontSize: 32)),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                  textDirection: TextDirection.rtl,
                ),
                Text(
                  desc,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey.shade600,
                      ),
                  textDirection: TextDirection.rtl,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

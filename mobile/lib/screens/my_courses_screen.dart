import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../app_theme.dart';
import '../api/config.dart';
import '../api/my_courses_api.dart';
import '../api/certificate_api.dart';
import 'feature_scaffold.dart';
import 'course_detail_screen.dart';

/// تعلّمي / دوراتي — بيانات من الـ backend (GET /api/my-courses)
class MyCoursesScreen extends StatefulWidget {
  const MyCoursesScreen({super.key});

  @override
  State<MyCoursesScreen> createState() => _MyCoursesScreenState();
}

class _MyCoursesScreenState extends State<MyCoursesScreen> {
  bool _loading = true;
  List<MyEnrollmentItem> _list = [];
  int _totalCourses = 0;
  int _completedCount = 0;
  int _inProgressCount = 0;
  double _avgProgress = 0;
  double _totalHours = 0;
  bool _needsAuth = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _downloadCertificate(String courseSlug) async {
    final res = await CertificateApi.getCertificateUrl(courseSlug);
    if (!mounted) return;
    if (res != null && res.url.isNotEmpty) {
      final uri = Uri.tryParse(res.url);
      if (uri != null && await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تعذر فتح الشهادة')));
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تعذر تحميل الشهادة')));
    }
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final res = await MyCoursesApi.getMyCourses();
    if (!mounted) return;
    setState(() {
      _list = res.enrollments;
      _totalCourses = res.totalCourses;
      _completedCount = res.completedCount;
      _inProgressCount = res.inProgressCount;
      _avgProgress = res.avgProgress;
      _totalHours = res.totalHours;
      _needsAuth = res.needsAuth;
      _loading = false;
    });
  }

  String _imageUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'تعلّمي / دوراتي',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _needsAuth
                ? _NeedsAuth(message: 'سجّل الدخول لعرض دوراتك')
                : _list.isEmpty
                    ? _EmptyState(
                        message: 'الدورات المسجلة فيها ستظهر هنا',
                        subtitle: 'بعد الاشتراك في أي دورة ستجدها هنا لمتابعة التعلم',
                        onRefresh: _load,
                      )
                    : CustomScrollView(
                        slivers: [
                          SliverToBoxAdapter(
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
                              child: _StatsCard(
                                totalCourses: _totalCourses,
                                completedCount: _completedCount,
                                inProgressCount: _inProgressCount,
                                avgProgress: _avgProgress,
                                totalHours: _totalHours,
                              ),
                            ),
                          ),
                          SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (_, i) {
                                final e = _list[i];
                                return _EnrollmentTile(
                                  item: e,
                                  imageUrl: _imageUrl(e.coverImage),
                                  onTap: () => Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => CourseDetailScreen(
                                        courseSlug: e.slug,
                                        courseTitle: e.title,
                                      ),
                                    ),
                                  ),
                                  onCertificate: e.completedAt != null
                                      ? () => _downloadCertificate(e.slug)
                                      : null,
                                );
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

class _StatsCard extends StatelessWidget {
  const _StatsCard({
    required this.totalCourses,
    required this.completedCount,
    required this.inProgressCount,
    required this.avgProgress,
    required this.totalHours,
  });

  final int totalCourses;
  final int completedCount;
  final int inProgressCount;
  final double avgProgress;
  final double totalHours;

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      color: AppTheme.primary.withValues(alpha: 0.08),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'ملخص التعلم',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryDark,
                  ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _StatChip(label: 'الدورات', value: '$totalCourses'),
                const SizedBox(width: 12),
                _StatChip(label: 'مكتمل', value: '$completedCount'),
                const SizedBox(width: 12),
                _StatChip(label: 'قيد التقدم', value: '$inProgressCount'),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                _StatChip(label: 'متوسط التقدم %', value: avgProgress.toStringAsFixed(1)),
                const SizedBox(width: 12),
                _StatChip(label: 'ساعات', value: totalHours.toStringAsFixed(1)),
              ],
            ),
          ],
        ),
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
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text('$label: $value', style: Theme.of(context).textTheme.labelMedium?.copyWith(fontWeight: FontWeight.w600)),
    );
  }
}

class _EnrollmentTile extends StatelessWidget {
  const _EnrollmentTile({required this.item, required this.imageUrl, required this.onTap, this.onCertificate});

  final MyEnrollmentItem item;
  final String imageUrl;
  final VoidCallback onTap;
  final VoidCallback? onCertificate;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 0,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: imageUrl.isNotEmpty
                      ? Image.network(imageUrl, width: 80, height: 80, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder())
                      : _placeholder(),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.title,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primaryDark,
                            ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item.instructorName,
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 6),
                      LinearProgressIndicator(
                        value: (item.progressPercentage / 100).clamp(0.0, 1.0),
                        backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                        color: AppTheme.primary,
                        borderRadius: BorderRadius.circular(2),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Text(
                            '${item.progressPercentage.toStringAsFixed(0)}%',
                            style: Theme.of(context).textTheme.labelSmall?.copyWith(color: AppTheme.primary),
                          ),
                          if (onCertificate != null) ...[
                            const SizedBox(width: 12),
                            TextButton.icon(
                              onPressed: onCertificate,
                              icon: const Icon(Icons.download_rounded, size: 18),
                              label: const Text('تحميل الشهادة'),
                              style: TextButton.styleFrom(
                                foregroundColor: AppTheme.primary,
                                padding: const EdgeInsets.symmetric(horizontal: 8),
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      width: 80,
      height: 80,
      color: AppTheme.primary.withValues(alpha: 0.15),
      child: Icon(Icons.school_rounded, color: AppTheme.primary.withValues(alpha: 0.6)),
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
          Icon(Icons.menu_book_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.6)),
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

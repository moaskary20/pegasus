import 'package:flutter/material.dart';
import '../api/instructor_api.dart';
import '../api/config.dart';
import '../app_theme.dart';
import 'course_detail_screen.dart';
import 'feature_scaffold.dart';

/// شاشة ملف المدرب — اسمه، صورته، ودوراته
class InstructorProfileScreen extends StatefulWidget {
  const InstructorProfileScreen({super.key, required this.instructorId});

  final int instructorId;

  @override
  State<InstructorProfileScreen> createState() => _InstructorProfileScreenState();
}

class _InstructorProfileScreenState extends State<InstructorProfileScreen> {
  InstructorProfile? _profile;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final res = await InstructorApi.getInstructor(widget.instructorId);
    if (!mounted) return;
    setState(() {
      _profile = res;
      _loading = res == null;
      _error = res == null ? 'لم يتم العثور على المدرب' : null;
    });
  }

  String _fullUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl : '$apiBaseUrl/';
    return path.startsWith('/') ? '$base${path.substring(1)}' : '$base$path';
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return FeatureScaffold(
        title: 'الملف الشخصي',
        body: const Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }
    if (_error != null || _profile == null) {
      return FeatureScaffold(
        title: 'الملف الشخصي',
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(_error ?? 'حدث خطأ', style: Theme.of(context).textTheme.titleMedium, textDirection: TextDirection.rtl),
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

    final inst = _profile!.instructor;

    return FeatureScaffold(
      title: inst.name,
      body: RefreshIndicator(
        onRefresh: _load,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              Center(
                child: ClipOval(
                  child: inst.avatar != null && inst.avatar!.isNotEmpty
                      ? Image.network(
                          _fullUrl(inst.avatar),
                          width: 100,
                          height: 100,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _avatarPlaceholder(),
                        )
                      : _avatarPlaceholder(),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                inst.name,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryDark,
                    ),
                textDirection: TextDirection.rtl,
              ),
              if (inst.bio != null && inst.bio!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  inst.bio!,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade600),
                  textAlign: TextAlign.center,
                  textDirection: TextDirection.rtl,
                ),
              ],
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _StatChip(icon: Icons.school_rounded, label: '${_profile!.coursesCount} دورة'),
                  const SizedBox(width: 12),
                  _StatChip(icon: Icons.people_outline_rounded, label: '${_profile!.totalStudents} طالب'),
                ],
              ),
              const SizedBox(height: 28),
              Align(
                alignment: Alignment.centerRight,
                child: Text(
                  'دورات المدرب',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryDark,
                      ),
                  textDirection: TextDirection.rtl,
                ),
              ),
              const SizedBox(height: 12),
              if (_profile!.courses.isEmpty)
                Padding(
                  padding: const EdgeInsets.all(32),
                  child: Text(
                    'لا توجد دورات',
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade600),
                    textDirection: TextDirection.rtl,
                  ),
                )
              else
                ...(_profile!.courses.map((c) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _CourseCard(
                        course: c,
                        imageUrl: _fullUrl(c.coverImage),
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => CourseDetailScreen(
                              courseSlug: c.slug,
                              courseTitle: c.title,
                            ),
                          ),
                        ),
                      ),
                    ))),
            ],
          ),
        ),
      ),
    );
  }

  Widget _avatarPlaceholder() {
    return Container(
      width: 100,
      height: 100,
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.2),
        shape: BoxShape.circle,
      ),
      child: Icon(Icons.person_rounded, size: 56, color: AppTheme.primary),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 20, color: AppTheme.primary),
          const SizedBox(width: 8),
          Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: AppTheme.primaryDark,
            ),
            textDirection: TextDirection.rtl,
          ),
        ],
      ),
    );
  }
}

class _CourseCard extends StatelessWidget {
  const _CourseCard({required this.course, required this.imageUrl, required this.onTap});

  final CourseListItem course;
  final String imageUrl;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            textDirection: TextDirection.rtl,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.network(
                  imageUrl,
                  width: 90,
                  height: 70,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(
                    width: 90,
                    height: 70,
                    color: Colors.grey.shade200,
                    child: const Icon(Icons.school_rounded, size: 32),
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      course.title,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        color: AppTheme.primaryDark,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      textDirection: TextDirection.rtl,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (course.rating != null) ...[
                          Icon(Icons.star_rounded, size: 16, color: Colors.amber.shade700),
                          const SizedBox(width: 4),
                          Text('${course.rating}', style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
                          const SizedBox(width: 12),
                        ],
                        Text(
                          '${course.price} ر.س',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primary,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}

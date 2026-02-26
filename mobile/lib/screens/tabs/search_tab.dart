import 'dart:async';
import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../../api/search_api.dart';
import '../../api/home_api.dart';
import '../../api/config.dart';
import '../../api/courses_api.dart';
import '../../app_theme.dart';
import '../course_detail_screen.dart';
import '../instructor_profile_screen.dart';
import '../lesson_player_screen.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب البحث — اقتراحات، فلاتر، ونتائج (دورات، دروس، مدربين، أسئلة)
class SearchTab extends StatefulWidget {
  const SearchTab({super.key, this.onOpenDrawer});

  final VoidCallback? onOpenDrawer;

  @override
  State<SearchTab> createState() => _SearchTabState();
}

class _SearchTabState extends State<SearchTab> {
  final _controller = TextEditingController();
  final _focusNode = FocusNode();
  SearchSuggestionsResult _suggestions = SearchSuggestionsResult(suggestions: [], recent: []);
  SearchResultsResponse? _results;
  bool _searching = false;
  Timer? _debounce;

  int? _categoryId;
  String? _level;
  bool? _isFree;
  double? _minRating;
  String? _sort;
  List<CourseCategoryItem> _categories = [];

  @override
  void initState() {
    super.initState();
    _loadSuggestions('');
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    final res = await CoursesApi.getCategories();
    if (mounted) setState(() => _categories = res.categories);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _onQueryChanged(String q) {
    setState(() {});
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      _loadSuggestions(q);
      if (q.length >= 2) {
        _doSearch(q);
      } else {
        setState(() {
          _results = null;
        });
      }
    });
  }

  Future<void> _loadSuggestions(String q) async {
    final res = await SearchApi.getSuggestions(q);
    if (mounted) setState(() => _suggestions = res);
  }

  Future<void> _clearSearchHistory() async {
    final ok = await SearchApi.clearHistory();
    if (mounted) {
      setState(() => _suggestions = SearchSuggestionsResult(suggestions: [], recent: []));
      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم مسح سجل البحث'), behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  Future<void> _doSearch(String q) async {
    if (q.trim().length < 2) return;
    setState(() => _searching = true);
    final res = await SearchApi.search(
      q.trim(),
      categoryId: _categoryId,
      level: _level,
      isFree: _isFree,
      minRating: _minRating,
      sort: _sort,
    );
    if (mounted) {
      setState(() {
        _results = res;
        _searching = false;
      });
    }
  }

  void _showFilters() {
    int? catId = _categoryId;
    String? level = _level;
    bool? isFree = _isFree;
    double? minRating = _minRating;
    String? sort = _sort;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModal) {
            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewPadding.bottom),
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('فلاتر البحث', style: Theme.of(ctx).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                        TextButton(
                          onPressed: () {
                            catId = null;
                            level = null;
                            isFree = null;
                            minRating = null;
                            sort = null;
                            setModal(() {});
                          },
                          child: const Text('مسح الكل'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    if (_categories.isNotEmpty) ...[
                      Text('التصنيف', style: Theme.of(ctx).textTheme.titleSmall?.copyWith(color: Colors.grey.shade700)),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          ChoiceChip(label: const Text('الكل'), selected: catId == null, onSelected: (_) => setModal(() => catId = null)),
                          ..._categories.map((c) => ChoiceChip(label: Text(c.name), selected: catId == c.id, onSelected: (_) => setModal(() => catId = c.id))),
                        ],
                      ),
                      const SizedBox(height: 16),
                    ],
                    Text('المستوى', style: Theme.of(ctx).textTheme.titleSmall?.copyWith(color: Colors.grey.shade700)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        ChoiceChip(label: const Text('الكل'), selected: level == null, onSelected: (_) => setModal(() => level = null)),
                        ChoiceChip(label: const Text('مبتدئ'), selected: level == 'beginner', onSelected: (_) => setModal(() => level = 'beginner')),
                        ChoiceChip(label: const Text('متوسط'), selected: level == 'intermediate', onSelected: (_) => setModal(() => level = 'intermediate')),
                        ChoiceChip(label: const Text('متقدم'), selected: level == 'advanced', onSelected: (_) => setModal(() => level = 'advanced')),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text('السعر', style: Theme.of(ctx).textTheme.titleSmall?.copyWith(color: Colors.grey.shade700)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        ChoiceChip(label: const Text('الكل'), selected: isFree == null, onSelected: (_) => setModal(() => isFree = null)),
                        ChoiceChip(label: const Text('مجاني فقط'), selected: isFree == true, onSelected: (_) => setModal(() => isFree = true)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text('الحد الأدنى للتقييم', style: Theme.of(ctx).textTheme.titleSmall?.copyWith(color: Colors.grey.shade700)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        ChoiceChip(label: const Text('الكل'), selected: minRating == null, onSelected: (_) => setModal(() => minRating = null)),
                        ...List.generate(5, (i) { final r = (i + 1).toDouble(); return ChoiceChip(label: Text('$r+'), selected: minRating == r, onSelected: (_) => setModal(() => minRating = r)); }),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text('الترتيب', style: Theme.of(ctx).textTheme.titleSmall?.copyWith(color: Colors.grey.shade700)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        ChoiceChip(label: const Text('الأكثر صلة'), selected: sort == null || sort == 'relevance', onSelected: (_) => setModal(() => sort = 'relevance')),
                        ChoiceChip(label: const Text('الأحدث'), selected: sort == 'newest', onSelected: (_) => setModal(() => sort = 'newest')),
                        ChoiceChip(label: const Text('الأعلى تقييماً'), selected: sort == 'rating', onSelected: (_) => setModal(() => sort = 'rating')),
                        ChoiceChip(label: const Text('الأكثر طلاباً'), selected: sort == 'students', onSelected: (_) => setModal(() => sort = 'students')),
                        ChoiceChip(label: const Text('السعر: من الأقل'), selected: sort == 'price_asc', onSelected: (_) => setModal(() => sort = 'price_asc')),
                        ChoiceChip(label: const Text('السعر: من الأعلى'), selected: sort == 'price_desc', onSelected: (_) => setModal(() => sort = 'price_desc')),
                      ],
                    ),
                    const SizedBox(height: 24),
                    FilledButton(
                      onPressed: () {
                        setState(() { _categoryId = catId; _level = level; _isFree = isFree; _minRating = minRating; _sort = sort; });
                        Navigator.pop(ctx);
                        if (_controller.text.trim().length >= 2) _doSearch(_controller.text.trim());
                      },
                      style: FilledButton.styleFrom(backgroundColor: _primary, padding: const EdgeInsets.symmetric(vertical: 14)),
                      child: const Text('تطبيق الفلاتر'),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  bool get _hasActiveFilters => _categoryId != null || _level != null || _isFree != null || _minRating != null || (_sort != null && _sort != 'relevance');

  String? _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    if (url.startsWith('http')) return url;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    return url.startsWith('/') ? '$base$url' : '$base/$url';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppHeader(
        title: 'البحث',
        onMenu: onOpenDrawer ?? () => Scaffold.of(context).openDrawer(),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _controller,
                    focusNode: _focusNode,
                    textDirection: TextDirection.rtl,
                    onChanged: _onQueryChanged,
                    decoration: InputDecoration(
                      hintText: 'ابحث عن دورة، درس، مدرب...',
                      prefixIcon: const Icon(Icons.search_rounded, color: _primary),
                      suffixIcon: _controller.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded),
                              onPressed: () {
                                _controller.clear();
                                _onQueryChanged('');
                              },
                            )
                          : null,
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: _primary, width: 2)),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filled(
                  onPressed: _showFilters,
                  icon: Badge(
                    isLabelVisible: _hasActiveFilters,
                    child: const Icon(Icons.filter_list_rounded),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                await _loadSuggestions(_controller.text);
                if (_controller.text.trim().length >= 2) await _doSearch(_controller.text.trim());
              },
              color: AppTheme.primary,
              child: _searching
                  ? SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: SizedBox(height: MediaQuery.of(context).size.height * 0.5, child: const Center(child: CircularProgressIndicator(color: AppTheme.primary))),
                    )
                  : _results != null
                      ? _buildResults()
                      : _buildSuggestions(),
            ),
          ),
        ],
      ),
    );
  }

  VoidCallback? get onOpenDrawer => widget.onOpenDrawer;

  Widget _buildSuggestions() {
    final recent = _suggestions.recent;
    final suggestions = _suggestions.suggestions;
    final showRecent = recent.isNotEmpty && _controller.text.trim().isEmpty;

    if (!showRecent && suggestions.isEmpty) {
      return SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: MediaQuery.of(context).size.height * 0.5,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.search_rounded, size: 80, color: _primary.withValues(alpha: 0.3)),
                const SizedBox(height: 16),
                Text(
                  'ابحث عن الدورات والمواد',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      children: [
        if (showRecent) ...[
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('عمليات بحث حديثة', style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: Colors.grey.shade700)),
              TextButton.icon(
                onPressed: _clearSearchHistory,
                icon: const Icon(Icons.delete_sweep_outlined, size: 18),
                label: const Text('مسح السجل'),
                style: TextButton.styleFrom(foregroundColor: Colors.grey.shade600),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ...recent.map((q) => ListTile(
                leading: const Icon(Icons.history_rounded, color: Colors.grey),
                title: Text(q, textDirection: TextDirection.rtl),
                onTap: () {
                  _controller.text = q;
                  _onQueryChanged(q);
                  _doSearch(q);
                },
              )),
          const SizedBox(height: 16),
        ],
        if (suggestions.isNotEmpty) ...[
          Text('اقتراحات', style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: Colors.grey.shade700)),
          const SizedBox(height: 8),
          ...suggestions.map((s) => ListTile(
                leading: const Icon(Icons.lightbulb_outline_rounded, color: Colors.grey),
                title: Text(s, textDirection: TextDirection.rtl),
                onTap: () {
                  _controller.text = s;
                  _onQueryChanged(s);
                  _doSearch(s);
                },
              )),
        ],
      ],
    );
  }

  Widget _buildResults() {
    final r = _results!;
    if (!r.hasResults) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.search_off_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'لا توجد نتائج لـ "${r.query}"',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    return Directionality(
      textDirection: TextDirection.rtl,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
        children: [
        if (r.instructors.isNotEmpty) ...[
          _sectionTitle('مدربون'),
          ...r.instructors.map((i) => _instructorCard(i)),
          const SizedBox(height: 16),
        ],
        if (r.courses.isNotEmpty) ...[
          _sectionTitle('دورات'),
          ...r.courses.map((c) => _courseCard(c)),
          const SizedBox(height: 16),
        ],
        if (r.lessons.isNotEmpty) ...[
          _sectionTitle('دروس'),
          ...r.lessons.map((l) => _lessonCard(l)),
          const SizedBox(height: 16),
        ],
        if (r.questions.isNotEmpty) ...[
          _sectionTitle('أسئلة'),
          ...r.questions.map((q) => _questionCard(q)),
        ],
      ],
      ),
    );
  }

  Widget _sectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Text(title, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: Colors.grey.shade700)),
    );
  }

  Widget _courseCard(CourseItem c) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: InkWell(
        onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => CourseDetailScreen(courseSlug: c.slug, courseTitle: c.title))),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            textDirection: TextDirection.rtl,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: _fullImageUrl(c.coverImage) != null
                    ? Image.network(_fullImageUrl(c.coverImage)!, width: 80, height: 80, fit: BoxFit.cover, errorBuilder: (context, error, stackTrace) => _placeholder())
                    : _placeholder(),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(c.title, maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
                    if (c.instructor != null) Text(c.instructor!.name, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    Row(
                      children: [
                        Icon(Icons.star_rounded, size: 16, color: Colors.orange.shade700),
                        const SizedBox(width: 4),
                        Text('${c.rating}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                        const SizedBox(width: 12),
                        Text('${c.price.toStringAsFixed(0)} ر.س', style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primary, fontSize: 13)),
                      ],
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

  Widget _lessonCard(SearchLessonItem l) {
    final slug = l.courseSlug ?? '';
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: InkWell(
        onTap: () {
          if (slug.isNotEmpty) {
            Navigator.of(context).push(MaterialPageRoute(
              builder: (_) => LessonPlayerScreen(courseSlug: slug, courseTitle: l.courseTitle, lessonId: l.id, lessonTitle: l.title),
            ));
          }
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            textDirection: TextDirection.rtl,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(color: _primary.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                child: Icon(Icons.play_circle_outline_rounded, size: 32, color: _primary),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(l.title, maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
                    Text(l.courseTitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    if (l.durationMinutes != null || l.isFree)
                      Row(
                        children: [
                          if (l.durationMinutes != null) Text('${l.durationMinutes} د', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                          if (l.durationMinutes != null && l.isFree) const SizedBox(width: 8),
                          if (l.isFree) Text('مجاني', style: TextStyle(fontSize: 11, color: Colors.green.shade700, fontWeight: FontWeight.w600)),
                        ],
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

  Widget _instructorCard(SearchInstructorItem i) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: InkWell(
        onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => InstructorProfileScreen(instructorId: i.id))),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            textDirection: TextDirection.rtl,
            children: [
              CircleAvatar(
                radius: 28,
                backgroundColor: Colors.grey.shade200,
                backgroundImage: _fullImageUrl(i.avatar) != null ? NetworkImage(_fullImageUrl(i.avatar)!) : null,
                child: _fullImageUrl(i.avatar) == null ? Text((i.name.isNotEmpty ? i.name[0] : '?').toUpperCase(), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)) : null,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(i.name, style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold)),
                    if (i.job != null && i.job!.isNotEmpty) Text(i.job!, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    if (i.coursesCount > 0) Text('${i.coursesCount} دورة', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                  ],
                ),
              ),
              Icon(Icons.arrow_back_ios_new_rounded, size: 16, color: Colors.grey.shade400),
            ],
          ),
        ),
      ),
    );
  }

  Widget _questionCard(SearchQuestionItem q) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: InkWell(
        onTap: () {
          final slug = q.courseSlug;
          if (slug != null && slug.isNotEmpty) {
            Navigator.of(context).push(MaterialPageRoute(builder: (_) => CourseDetailScreen(courseSlug: slug, courseTitle: q.courseTitle)));
          }
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            textDirection: TextDirection.rtl,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(color: Colors.amber.shade100, borderRadius: BorderRadius.circular(10)),
                child: Icon(Icons.help_outline_rounded, color: Colors.amber.shade800, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(q.question, maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w500)),
                    const SizedBox(height: 4),
                    Text(q.courseTitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    if (q.answersCount > 0) Text('${q.answersCount} إجابة', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      width: 80,
      height: 80,
      color: Colors.grey.shade200,
      child: Icon(Icons.school_rounded, color: Colors.grey.shade400),
    );
  }
}

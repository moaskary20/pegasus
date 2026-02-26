import 'dart:async';
import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../../api/search_api.dart';
import '../../api/config.dart';
import '../../app_theme.dart';
import '../course_detail_screen.dart';

const Color _primary = Color(0xFF2c004d);

/// تبويب البحث — اقتراحات ونتائج مرتبطة بالـ API
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

  @override
  void initState() {
    super.initState();
    _loadSuggestions('');
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

  Future<void> _doSearch(String q) async {
    if (q.trim().length < 2) return;
    setState(() => _searching = true);
    final res = await SearchApi.search(q.trim());
    if (mounted) setState(() {
      _results = res;
      _searching = false;
    });
  }

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
            child: TextField(
              controller: _controller,
              focusNode: _focusNode,
              textDirection: TextDirection.rtl,
              onChanged: _onQueryChanged,
              decoration: InputDecoration(
                hintText: 'ابحث عن دورة أو مدرب...',
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
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: const BorderSide(color: _primary, width: 2),
                ),
              ),
            ),
          ),
          Expanded(
            child: _searching
                ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
                : _results != null
                    ? _buildResults()
                    : _buildSuggestions(),
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
      return Center(
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
      );
    }

    return ListView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      children: [
        if (showRecent) ...[
          Text('عمليات بحث حديثة', style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold, color: Colors.grey.shade700)),
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
    final courses = _results!.courses;
    if (courses.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.search_off_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'لا توجد نتائج لـ "${_results!.query}"',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      itemCount: courses.length,
      itemBuilder: (context, i) {
        final c = courses[i];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
          child: InkWell(
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => CourseDetailScreen(courseSlug: c.slug, courseTitle: c.title),
              ),
            ),
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                textDirection: TextDirection.rtl,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: _fullImageUrl(c.coverImage) != null
                        ? Image.network(_fullImageUrl(c.coverImage)!, width: 80, height: 80, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder())
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
      },
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

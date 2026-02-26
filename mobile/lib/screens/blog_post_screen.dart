import 'package:flutter/material.dart';
import '../api/blog_api.dart';
import '../api/config.dart';
import '../app_theme.dart';

/// شاشة عرض مقال المدونة
class BlogPostScreen extends StatefulWidget {
  const BlogPostScreen({super.key, required this.slug, this.title});

  final String slug;
  final String? title;

  @override
  State<BlogPostScreen> createState() => _BlogPostScreenState();
}

class _BlogPostScreenState extends State<BlogPostScreen> {
  BlogPostItem? _post;
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
    final post = await BlogApi.getPostBySlug(widget.slug);
    if (!mounted) return;
    setState(() {
      _post = post;
      _loading = false;
      _error = post == null ? 'تعذر تحميل المقال' : null;
    });
  }

  String _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http')) return url;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    return url.startsWith('/') ? '$base$url' : '$base/$url';
  }

  String _stripHtml(String html) {
    return html.replaceAll(RegExp(r'<[^>]*>'), ' ').replaceAll(RegExp(r'\s+'), ' ').trim();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        title: Text(widget.title ?? 'المقال', maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade400),
                        const SizedBox(height: 16),
                        Text(_error ?? '', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade700)),
                        const SizedBox(height: 24),
                        FilledButton.icon(
                          onPressed: _load,
                          icon: const Icon(Icons.refresh_rounded),
                          label: const Text('إعادة المحاولة'),
                          style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  color: AppTheme.primary,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_post!.coverImage != null && _post!.coverImage!.isNotEmpty)
                          Image.network(
                            _fullImageUrl(_post!.coverImage),
                            width: double.infinity,
                            height: 220,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => _placeholder(),
                          )
                        else
                          _placeholder(),
                        Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _post!.title,
                                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                      fontWeight: FontWeight.bold,
                                      color: const Color(0xFF1A1A1A),
                                    ),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  if (_post!.author != null) ...[
                                    CircleAvatar(
                                      radius: 18,
                                      backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                                      backgroundImage: _post!.author!.avatar != null && _post!.author!.avatar!.isNotEmpty
                                          ? NetworkImage(_fullImageUrl(_post!.author!.avatar))
                                          : null,
                                      child: _post!.author!.avatar == null || _post!.author!.avatar!.isEmpty
                                          ? Text(
                                              _post!.author!.name.isNotEmpty ? _post!.author!.name[0].toUpperCase() : '?',
                                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                            )
                                          : null,
                                    ),
                                    const SizedBox(width: 12),
                                    Text(
                                      _post!.author!.name,
                                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.grey.shade700),
                                    ),
                                    const SizedBox(width: 16),
                                  ],
                                  if (_post!.formattedDate.isNotEmpty)
                                    Text(
                                      _post!.formattedDate,
                                      style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 24),
                              if (_post!.content != null && _post!.content!.isNotEmpty)
                                SelectableText(
                                  _stripHtml(_post!.content!),
                                  style: TextStyle(fontSize: 16, height: 1.7, color: Colors.grey.shade800),
                                )
                              else if (_post!.excerpt != null)
                                SelectableText(
                                  _post!.excerpt!,
                                  style: TextStyle(fontSize: 16, height: 1.7, color: Colors.grey.shade800),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 32),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _placeholder() {
    return Container(
      width: double.infinity,
      height: 160,
      color: AppTheme.primary.withValues(alpha: 0.08),
      child: Icon(Icons.article_rounded, size: 56, color: AppTheme.primary.withValues(alpha: 0.3)),
    );
  }
}

import 'package:flutter/material.dart';
import '../api/blog_api.dart';
import '../api/config.dart';
import '../app_theme.dart';
import 'blog_post_screen.dart';
import 'feature_scaffold.dart';

/// شاشة قائمة مقالات المدونة
class BlogListScreen extends StatefulWidget {
  const BlogListScreen({super.key});

  @override
  State<BlogListScreen> createState() => _BlogListScreenState();
}

class _BlogListScreenState extends State<BlogListScreen> {
  List<BlogPostItem> _posts = [];
  int _currentPage = 1;
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
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
    final res = await BlogApi.getPosts(page: 1);
    if (!mounted) return;
    setState(() {
      _posts = res.posts;
      _currentPage = res.currentPage;
      _lastPage = res.lastPage;
      _loading = false;
      _error = res.loadError;
    });
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _currentPage >= _lastPage) return;
    setState(() => _loadingMore = true);
    final res = await BlogApi.getPosts(page: _currentPage + 1);
    if (!mounted) return;
    setState(() {
      _posts = [..._posts, ...res.posts];
      _currentPage = res.currentPage;
      _lastPage = res.lastPage;
      _loadingMore = false;
    });
  }

  String _fullImageUrl(String? url) {
    if (url == null || url.isEmpty) return '';
    if (url.startsWith('http')) return url;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    return url.startsWith('/') ? '$base$url' : '$base/$url';
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: 'المدونة',
      body: RefreshIndicator(
        onRefresh: _load,
        color: AppTheme.primary,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : _error != null
                ? _buildErrorState()
                : _posts.isEmpty
                    ? _buildEmptyState()
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                        itemCount: _posts.length + (_loadingMore ? 1 : 0) + (_currentPage < _lastPage && !_loadingMore ? 1 : 0),
                        itemBuilder: (context, index) {
                          if (index >= _posts.length) {
                            if (_loadingMore) {
                              return const Padding(
                                padding: EdgeInsets.all(24),
                                child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                              );
                            }
                            return _buildLoadMoreButton();
                          }
                          return _buildPostCard(_posts[index]);
                        },
                      ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(_error ?? 'حدث خطأ', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade700)),
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
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.article_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text('لا توجد مقالات بعد', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.grey.shade700)),
            const SizedBox(height: 8),
            Text('سيتم إضافة محتوى المدونة قريباً', style: TextStyle(color: Colors.grey.shade600)),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadMoreButton() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Center(
        child: TextButton.icon(
          onPressed: _loadMore,
          icon: const Icon(Icons.add_circle_outline_rounded),
          label: const Text('تحميل المزيد'),
          style: TextButton.styleFrom(foregroundColor: AppTheme.primary),
        ),
      ),
    );
  }

  Widget _buildPostCard(BlogPostItem post) {
    final coverUrl = post.coverImage != null && post.coverImage!.isNotEmpty ? _fullImageUrl(post.coverImage) : null;
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => BlogPostScreen(slug: post.slug, title: post.title),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (coverUrl != null && coverUrl.isNotEmpty)
              Image.network(
                coverUrl,
                height: 160,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => _buildPlaceholder(),
              )
            else
              _buildPlaceholder(),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    post.title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF1A1A1A),
                        ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (post.excerpt != null && post.excerpt!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      post.excerpt!,
                      style: TextStyle(fontSize: 14, color: Colors.grey.shade600, height: 1.4),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      if (post.author != null) ...[
                        CircleAvatar(
                          radius: 14,
                          backgroundColor: AppTheme.primary.withValues(alpha: 0.2),
                          backgroundImage: post.author!.avatar != null && post.author!.avatar!.isNotEmpty
                              ? NetworkImage(_fullImageUrl(post.author!.avatar))
                              : null,
                          child: post.author!.avatar == null || post.author!.avatar!.isEmpty
                              ? Text(
                                  post.author!.name.isNotEmpty ? post.author!.name[0].toUpperCase() : '?',
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                )
                              : null,
                        ),
                        const SizedBox(width: 8),
                        Text(post.author!.name, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                        const SizedBox(width: 12),
                      ],
                      if (post.formattedDate.isNotEmpty)
                        Text(post.formattedDate, style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      height: 160,
      width: double.infinity,
      color: AppTheme.primary.withValues(alpha: 0.1),
      child: Icon(Icons.article_rounded, size: 48, color: AppTheme.primary.withValues(alpha: 0.4)),
    );
  }
}

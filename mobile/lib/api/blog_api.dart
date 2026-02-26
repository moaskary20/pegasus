import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import '../utils/error_messages.dart';

class BlogApi {
  BlogApi._();

  static final _headers = {'Accept': 'application/json'};
  static String get _base => apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;

  /// قائمة مقالات المدونة (مع صفحة)
  static Future<BlogListResponse> getPosts({int page = 1, int perPage = 15}) async {
    try {
      final uri = Uri.parse('$_base$apiBlog').replace(queryParameters: {'page': page.toString(), 'per_page': perPage.toString()});
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final posts = (data['posts'] as List<dynamic>?) ?? [];
        return BlogListResponse(
          posts: posts.map((e) => BlogPostItem.fromJson(e as Map<String, dynamic>)).toList(),
          currentPage: (data['current_page'] as num?)?.toInt() ?? 1,
          lastPage: (data['last_page'] as num?)?.toInt() ?? 1,
          total: (data['total'] as num?)?.toInt() ?? 0,
          loadError: null,
        );
      }
      return BlogListResponse(posts: [], currentPage: 1, lastPage: 1, total: 0, loadError: ErrorMessages.from(null, statusCode: res.statusCode, fallback: 'تعذر تحميل المدونة'));
    } catch (e) {
      return BlogListResponse(posts: [], currentPage: 1, lastPage: 1, total: 0, loadError: ErrorMessages.from(e));
    }
  }

  /// مقال واحد بالـ slug
  static Future<BlogPostItem?> getPostBySlug(String slug) async {
    try {
      final uri = Uri.parse('$_base$apiBlog/$slug');
      final res = await http.get(uri, headers: _headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
        return BlogPostItem.fromJson(data, full: true);
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}

class BlogListResponse {
  BlogListResponse({required this.posts, required this.currentPage, required this.lastPage, required this.total, this.loadError});
  final List<BlogPostItem> posts;
  final int currentPage;
  final int lastPage;
  final int total;
  final String? loadError;
}

class BlogPostItem {
  BlogPostItem({
    required this.id,
    required this.title,
    required this.slug,
    this.excerpt,
    this.coverImage,
    this.publishedAt,
    this.author,
    this.content,
  });

  final int id;
  final String title;
  final String slug;
  final String? excerpt;
  final String? coverImage;
  final String? publishedAt;
  final BlogAuthor? author;
  final String? content;

  factory BlogPostItem.fromJson(Map<String, dynamic> json, {bool full = false}) {
    BlogAuthor? author;
    if (json['author'] != null) {
      author = BlogAuthor.fromJson(json['author'] as Map<String, dynamic>);
    }
    return BlogPostItem(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      slug: (json['slug'] ?? '').toString(),
      excerpt: json['excerpt']?.toString(),
      coverImage: json['cover_image']?.toString(),
      publishedAt: json['published_at']?.toString(),
      author: author,
      content: full ? json['content']?.toString() : null,
    );
  }

  String get formattedDate {
    if (publishedAt == null || publishedAt!.isEmpty) return '';
    try {
      final dt = DateTime.parse(publishedAt!);
      return '${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}';
    } catch (_) {
      return publishedAt!;
    }
  }
}

class BlogAuthor {
  BlogAuthor({required this.id, required this.name, this.avatar});
  final int id;
  final String name;
  final String? avatar;
  factory BlogAuthor.fromJson(Map<String, dynamic> json) => BlogAuthor(
        id: (json['id'] as num?)?.toInt() ?? 0,
        name: (json['name'] ?? '').toString(),
        avatar: json['avatar']?.toString(),
      );
}

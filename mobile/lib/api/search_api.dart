import 'dart:convert';
import 'package:http/http.dart' as http;
import 'config.dart';
import 'auth_api.dart';
import 'home_api.dart';

class SearchApi {
  SearchApi._();

  static Map<String, String> get _headers {
    final h = {'Accept': 'application/json'};
    if (AuthApi.token != null) h['Authorization'] = 'Bearer ${AuthApi.token}';
    return h;
  }

  /// اقتراحات البحث أثناء الكتابة (recent, popular, courses من الباكند)
  static Future<SearchSuggestionsResult> getSuggestions(String query) async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSearchSuggestions').replace(queryParameters: {'q': query});
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString());
      if (res.statusCode == 200 && data is Map<String, dynamic>) {
        String toStr(dynamic e) => e is Map ? (e['text'] ?? e['query'] ?? '').toString() : e.toString();
        final recent = ((data['recent'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final popular = ((data['popular'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final courses = ((data['courses'] as List<dynamic>?) ?? []).map(toStr).where((t) => t.trim().isNotEmpty).toList();
        final suggestions = [...popular, ...courses];
        return SearchSuggestionsResult(suggestions: suggestions, recent: recent);
      }
    } catch (_) {}
    return SearchSuggestionsResult(suggestions: [], recent: []);
  }

  /// نتائج البحث (دورات، دروس، مدربين...)
  static Future<SearchResultsResponse> search(String query, {int? categoryId, String? level, bool? isFree, double? minRating, int? instructorId, String? sort}) async {
    try {
      final params = <String, String>{'q': query};
      if (categoryId != null && categoryId > 0) params['category_id'] = categoryId.toString();
      if (level != null && level.isNotEmpty) params['level'] = level;
      if (isFree != null) params['is_free'] = isFree.toString();
      if (minRating != null && minRating > 0) params['min_rating'] = minRating.toString();
      if (instructorId != null && instructorId > 0) params['instructor_id'] = instructorId.toString();
      if (sort != null && sort.isNotEmpty) params['sort'] = sort;

      final uri = Uri.parse('$apiBaseUrl$apiSearchResults').replace(queryParameters: params);
      final res = await http.get(uri, headers: _headers);
      final data = jsonDecode(res.body.toString()) as Map<String, dynamic>? ?? {};
      if (res.statusCode == 200) {
        final coursesRaw = (data['results']?['courses'] ?? data['courses']) as List<dynamic>? ?? [];
        final courses = coursesRaw.map((e) => CourseItem.fromJson(e as Map<String, dynamic>)).toList();
        return SearchResultsResponse(query: (data['query'] ?? query).toString(), courses: courses);
      }
    } catch (_) {}
    return SearchResultsResponse(query: query, courses: []);
  }

  /// مسح سجل البحث (يتطلب مصادقة)
  static Future<bool> clearHistory() async {
    try {
      final uri = Uri.parse('$apiBaseUrl$apiSearchClearHistory');
      final res = await http.post(uri, headers: _headers);
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}

class SearchSuggestionsResult {
  SearchSuggestionsResult({required this.suggestions, required this.recent});
  final List<String> suggestions;
  final List<String> recent;
}

class SearchResultsResponse {
  SearchResultsResponse({required this.query, required this.courses});
  final String query;
  final List<CourseItem> courses;
}

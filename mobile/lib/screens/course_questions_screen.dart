import 'package:flutter/material.dart';
import '../api/course_questions_api.dart';
import '../api/auth_api.dart';
import '../app_theme.dart';

/// شاشة أسئلة وأجوبة الدرس (Q&A)
class CourseQuestionsScreen extends StatefulWidget {
  const CourseQuestionsScreen({
    super.key,
    required this.courseSlug,
    required this.courseTitle,
    required this.lessonId,
    this.lessonTitle,
  });

  final String courseSlug;
  final String courseTitle;
  final int lessonId;
  final String? lessonTitle;

  @override
  State<CourseQuestionsScreen> createState() => _CourseQuestionsScreenState();
}

class _CourseQuestionsScreenState extends State<CourseQuestionsScreen> {
  List<CourseQuestionItem>? _questions;
  bool _loading = true;
  String? _error;
  final TextEditingController _questionController = TextEditingController();
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _questionController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    if (AuthApi.token == null) {
      setState(() {
        _loading = false;
        _error = 'يجب تسجيل الدخول لعرض الأسئلة';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    final list = await CourseQuestionsApi.getQuestions(widget.courseSlug, widget.lessonId);
    if (!mounted) return;
    setState(() {
      _questions = list;
      _loading = false;
    });
  }

  Future<void> _submitQuestion() async {
    final text = _questionController.text.trim();
    if (text.isEmpty || _submitting) return;
    setState(() => _submitting = true);
    final added = await CourseQuestionsApi.addQuestion(widget.courseSlug, widget.lessonId, text);
    if (!mounted) return;
    if (added != null) {
      _questionController.clear();
      setState(() {
        _questions = [...?(_questions ?? []), added];
        _submitting = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم إرسال سؤالك. سيرد عليه المدرب قريباً.')),
        );
      }
    } else {
      setState(() => _submitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تعذر إرسال السؤال. حاول مجدداً.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.lessonTitle ?? 'أسئلة وأجوبة'),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline_rounded, size: 64, color: Colors.grey[600]),
                        const SizedBox(height: 16),
                        Text(_error!, textAlign: TextAlign.center, style: TextStyle(color: Colors.grey[700], fontSize: 16)),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // نموذج طرح سؤال
                      Card(
                        elevation: 1,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Text('اطرح سؤالك', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                              const SizedBox(height: 12),
                              TextField(
                                controller: _questionController,
                                maxLines: 3,
                                decoration: InputDecoration(
                                  hintText: 'اكتب سؤالك عن هذا الدرس...',
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                  filled: true,
                                ),
                              ),
                              const SizedBox(height: 12),
                              FilledButton(
                                onPressed: _submitting ? null : _submitQuestion,
                                style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                                child: _submitting ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('طرح السؤال'),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      Text('الأسئلة والأجوبة', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 12),
                      if (_questions == null || _questions!.isEmpty)
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 32),
                          child: Center(
                            child: Text('لا توجد أسئلة بعد. كن أول من يطرح سؤالاً!', style: TextStyle(color: Colors.grey[600])),
                          ),
                        )
                      else
                        ..._questions!.map((q) => _buildQuestionCard(q)),
                    ],
                  ),
                ),
    );
  }

  Widget _buildQuestionCard(CourseQuestionItem q) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppTheme.primary.withOpacity(0.2),
                  child: Text(
                    (q.userName.isEmpty ? '?' : q.userName[0]).toUpperCase(),
                    style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(q.userName, style: const TextStyle(fontWeight: FontWeight.bold)),
                      if (q.createdAt != null) Text(q.createdAt!, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                    ],
                  ),
                ),
                if (q.isAnswered)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(color: Colors.green.shade100, borderRadius: BorderRadius.circular(8)),
                    child: Text('تم الرد', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.green.shade800)),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Text(q.question, style: const TextStyle(fontSize: 15)),
            if (q.answers.isNotEmpty) ...[
              const SizedBox(height: 12),
              ...q.answers.map((a) => Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.06),
                        borderRadius: BorderRadius.circular(8),
                        border: Border(right: BorderSide(color: AppTheme.primary.withOpacity(0.3), width: 3)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(a.userName, style: TextStyle(fontSize: 12, color: Colors.grey[700], fontWeight: FontWeight.w600)),
                          const SizedBox(height: 4),
                          Text(a.answer, style: const TextStyle(fontSize: 14)),
                        ],
                      ),
                    ),
                  )),
            ],
          ],
        ),
      ),
    );
  }
}

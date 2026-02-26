import 'dart:async';
import 'package:flutter/material.dart';
import '../api/quiz_api.dart';
import '../api/auth_api.dart';
import '../app_theme.dart';
import 'feature_scaffold.dart';
import 'course_detail_screen.dart';

/// شاشة الاختبار — عرض الأسئلة وإرسال الإجابات وعرض النتيجة
class QuizScreen extends StatefulWidget {
  const QuizScreen({
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
  State<QuizScreen> createState() => _QuizScreenState();
}

class _QuizScreenState extends State<QuizScreen> {
  QuizResponse? _response;
  bool _loading = true;
  String? _error;
  final Map<int, dynamic> _answers = {};
  bool _submitting = false;
  QuizSubmitResult? _submitResult;
  Timer? _timer;
  int? _timeRemainingSeconds;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    if (AuthApi.token == null) {
      setState(() {
        _loading = false;
        _error = 'يجب تسجيل الدخول لأداء الاختبار';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
      _submitResult = null;
    });
    final result = await QuizApi.getQuiz(widget.courseSlug, widget.lessonId);
    if (!mounted) return;
    if (result.error != null) {
      setState(() {
        _loading = false;
        _error = result.error;
      });
      return;
    }
    final res = result.response!;
    if (res.maxReached) {
      setState(() {
        _loading = false;
        _response = res;
      });
      return;
    }
    if (res.alreadySubmitted && res.lastAttempt != null) {
      final att = res.lastAttempt!;
      final passPct = res.quiz?.passPercentage ?? 0;
      setState(() {
        _loading = false;
        _submitResult = QuizSubmitResult(
          score: att.score ?? 0,
          passed: att.passed,
          passPercentage: passPct,
          attempt: att,
        );
      });
      return;
    }
    _response = res;
    if (res.attempt != null) {
      _timeRemainingSeconds = res.attempt!.timeRemainingSeconds;
      if (_timeRemainingSeconds != null && _timeRemainingSeconds! > 0) {
        _timer = Timer.periodic(const Duration(seconds: 1), (_) {
          if (!mounted) return;
          setState(() {
            _timeRemainingSeconds = (_timeRemainingSeconds ?? 1) - 1;
            if (_timeRemainingSeconds! <= 0) _timer?.cancel();
          });
        });
      }
    }
    setState(() => _loading = false);
  }

  Future<void> _submit() async {
    if (_response?.quiz == null || _submitting) return;
    setState(() => _submitting = true);
    final result = await QuizApi.submitQuiz(widget.courseSlug, widget.lessonId, _answers);
    if (!mounted) return;
    setState(() {
      _submitting = false;
      _submitResult = result;
    });
  }

  Future<void> _retake() async {
    setState(() {
      _submitResult = null;
      _response = null;
      _answers.clear();
      _loading = true;
    });
    final retakeRes = await QuizApi.retakeQuiz(widget.courseSlug, widget.lessonId);
    if (!mounted) return;
    if (retakeRes?.success == true) {
      _load();
    } else {
      setState(() {
        _loading = false;
        _error = 'تعذر إعادة المحاولة';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return FeatureScaffold(
      title: widget.lessonTitle ?? 'اختبار الدرس',
      onBack: () => _goBack(),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? _buildError()
              : _submitResult != null
                  ? _buildResult()
                  : _buildQuiz(),
    );
  }

  void _goBack() {
    Navigator.of(context).popUntil((r) => r.isFirst || r.settings.name == '/');
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (_) => CourseDetailScreen(courseSlug: widget.courseSlug, courseTitle: widget.courseTitle),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline_rounded, size: 64, color: AppTheme.primary.withValues(alpha: 0.7)),
            const SizedBox(height: 16),
            Text(_error!, textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                OutlinedButton(
                  onPressed: () {
                    setState(() {
                      _error = null;
                      _loading = true;
                    });
                    _load();
                  },
                  style: OutlinedButton.styleFrom(foregroundColor: AppTheme.primary),
                  child: const Text('إعادة المحاولة'),
                ),
                const SizedBox(width: 12),
                FilledButton(
                  onPressed: () => Navigator.pop(context),
                  style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                  child: const Text('رجوع'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResult() {
    final r = _submitResult!;
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const SizedBox(height: 24),
          Icon(
            r.passed ? Icons.celebration_rounded : Icons.sentiment_dissatisfied_rounded,
            size: 80,
            color: r.passed ? Colors.green : Colors.orange,
          ),
          const SizedBox(height: 16),
          Text(
            r.passed ? 'تهانينا! نجحت في الاختبار' : 'لم تنجح في الاختبار',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            'نقاطك: ${r.score.toStringAsFixed(1)}% (المطلوب: ${r.passPercentage.toStringAsFixed(0)}%)',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.grey.shade700),
          ),
          const SizedBox(height: 32),
          if (_response?.quiz?.allowRetake == true)
            FilledButton.icon(
              onPressed: _retake,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('إعادة المحاولة'),
              style: FilledButton.styleFrom(backgroundColor: AppTheme.primary, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
            ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: _goBack,
            icon: const Icon(Icons.arrow_back_rounded),
            label: const Text('العودة للدورة'),
            style: OutlinedButton.styleFrom(foregroundColor: AppTheme.primary),
          ),
        ],
      ),
    );
  }

  Widget _buildQuiz() {
    final quiz = _response?.quiz;
    if (quiz == null) return _buildError();

    return Column(
      children: [
        if (_timeRemainingSeconds != null && _timeRemainingSeconds! > 0)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: AppTheme.primary.withValues(alpha: 0.1),
            child: Row(
              children: [
                Icon(Icons.timer_rounded, color: AppTheme.primary, size: 20),
                const SizedBox(width: 8),
                Text(
                  'متبقي: ${_formatTime(_timeRemainingSeconds!)}',
                  style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primary),
                ),
              ],
            ),
          ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(20),
            itemCount: quiz.questions.length,
            itemBuilder: (context, i) {
              final q = quiz.questions[i];
              return Padding(
                padding: const EdgeInsets.only(bottom: 20),
                child: _QuestionCard(
                  question: q,
                  index: i + 1,
                  value: _answers[q.id],
                  onChanged: (v) => setState(() => _answers[q.id] = v),
                ),
              );
            },
          ),
        ),
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(color: Colors.white, boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 8, offset: const Offset(0, -2))]),
          child: SafeArea(
            child: FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(backgroundColor: AppTheme.primary, padding: const EdgeInsets.symmetric(vertical: 16)),
              child: _submitting
                  ? const SizedBox(height: 24, width: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('إرسال الإجابات', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          ),
        ),
      ],
    );
  }

  String _formatTime(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }
}

class _QuestionCard extends StatelessWidget {
  const _QuestionCard({
    required this.question,
    required this.index,
    required this.value,
    required this.onChanged,
  });

  final QuizQuestionItem question;
  final int index;
  final dynamic value;
  final ValueChanged<dynamic> onChanged;

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '$index. ${question.questionText}',
              style: Theme.of(context).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.bold),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 12),
            if (question.type == 'mcq' || question.type == 'true_false')
              ...(question.options as List<dynamic>? ?? []).map((opt) {
                final str = opt is Map ? (opt['text'] ?? opt['label'] ?? '').toString() : opt.toString();
                if (str.isEmpty) return const SizedBox.shrink();
                return RadioListTile<String>(
                  value: str,
                  groupValue: value != null ? value.toString() : null,
                  onChanged: (v) => onChanged(v),
                  title: Text(str, textDirection: TextDirection.rtl),
                  dense: true,
                  contentPadding: EdgeInsets.zero,
                );
              }),
            if (question.type == 'fill_blank' || question.type == 'short_answer')
              TextField(
                textDirection: TextDirection.rtl,
                decoration: const InputDecoration(
                  hintText: 'أدخل إجابتك',
                  border: OutlineInputBorder(),
                ),
                onChanged: (v) => onChanged(v.trim().isEmpty ? null : v.trim()),
              ),
          ],
        ),
      ),
    );
  }
}

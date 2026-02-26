import 'dart:async';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';
import '../api/config.dart';
import '../api/lessons_api.dart';
import '../api/auth_api.dart';
import '../app_theme.dart';
import 'quiz_screen.dart';

/// شاشة مشغل الدرس — فيديو كامل مع حفظ التقدم
class LessonPlayerScreen extends StatefulWidget {
  const LessonPlayerScreen({
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
  State<LessonPlayerScreen> createState() => _LessonPlayerScreenState();
}

class _LessonPlayerScreenState extends State<LessonPlayerScreen> {
  LessonDetailItem? _lesson;
  bool _loading = true;
  String? _error;
  VideoPlayerController? _videoController;
  YoutubePlayerController? _youtubeController;
  Timer? _progressTimer;
  bool _isYoutube = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _progressTimer?.cancel();
    _saveProgressIfNeeded();
    _videoController?.dispose();
    _youtubeController?.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    if (AuthApi.token == null) {
      setState(() {
        _loading = false;
        _error = 'يجب تسجيل الدخول لمشاهدة الدرس';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    final lesson = await LessonsApi.getLesson(widget.courseSlug, widget.lessonId);
    if (!mounted) return;
    if (lesson == null) {
      setState(() {
        _loading = false;
        _error = 'تعذر تحميل الدرس أو لا يوجد صلاحية';
      });
      return;
    }
    _lesson = lesson;
    if (lesson.videoUrl != null && lesson.videoUrl!.isNotEmpty) {
      _initPlayer(lesson.videoUrl!);
    }
    setState(() => _loading = false);
    _startProgressTimer();
  }

  void _initPlayer(String url) {
    final videoId = YoutubePlayer.convertUrlToId(url);
    if (videoId != null) {
      _isYoutube = true;
      _youtubeController = YoutubePlayerController(
        initialVideoId: videoId,
        flags: const YoutubePlayerFlags(
          autoPlay: true,
          mute: false,
          controlsVisibleAtStart: true,
        ),
      );
    } else {
      _isYoutube = false;
      final fullUrl = _fullUrl(url);
      _videoController = VideoPlayerController.networkUrl(Uri.parse(fullUrl))
        ..initialize().then((_) {
          if (mounted) setState(() {});
          _videoController!.play();
        }).catchError((_) {
          if (mounted) setState(() => _error = 'تعذر تشغيل الفيديو');
        });
    }
  }

  String _fullUrl(String url) {
    if (url.startsWith('http')) return url;
    final base = apiBaseUrl.endsWith('/') ? apiBaseUrl.substring(0, apiBaseUrl.length - 1) : apiBaseUrl;
    return url.startsWith('/') ? '$base$url' : '$base/$url';
  }

  void _startProgressTimer() {
    _progressTimer?.cancel();
    _progressTimer = Timer.periodic(const Duration(seconds: 15), (_) => _saveProgressIfNeeded());
  }

  Future<void> _saveProgressIfNeeded() async {
    if (_lesson == null || AuthApi.token == null) return;
    int position = 0;
    int duration = _lesson!.durationMinutes * 60;
    if (_isYoutube && _youtubeController != null) {
      try {
        position = _youtubeController!.value.position.inSeconds;
        duration = _youtubeController!.metadata.duration.inSeconds;
      } catch (_) {}
    } else if (_videoController != null && _videoController!.value.isInitialized) {
      position = _videoController!.value.position.inSeconds;
      duration = _videoController!.value.duration.inSeconds;
    }
    await LessonsApi.saveProgress(widget.courseSlug, widget.lessonId, position, duration);
  }

  void _onExit() {
    _saveProgressIfNeeded();
    Navigator.of(context).pop();
  }

  Future<void> _checkQuizAndNavigate() async {
    if (_lesson?.hasQuiz != true) {
      _onExit();
      return;
    }
    await _saveProgressIfNeeded();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (_) => QuizScreen(
          courseSlug: widget.courseSlug,
          courseTitle: widget.courseTitle,
          lessonId: widget.lessonId,
          lessonTitle: _lesson?.title ?? widget.lessonTitle,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
            onPressed: _onExit,
          ),
          title: Text(
            widget.lessonTitle ?? 'الدرس',
            style: const TextStyle(color: Colors.white, fontSize: 16),
          ),
        ),
        body: const Center(child: CircularProgressIndicator(color: Colors.white)),
      );
    }

    if (_error != null) {
      return Scaffold(
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          leading: IconButton(icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white), onPressed: () => Navigator.pop(context)),
          title: const Text('الدرس', style: TextStyle(color: Colors.white)),
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.error_outline_rounded, size: 64, color: Colors.white70),
                const SizedBox(height: 16),
                Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 16)),
              ],
            ),
          ),
        ),
      );
    }

    final lesson = _lesson!;
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        _saveProgressIfNeeded();
        Navigator.of(context).pop();
      },
      child: Scaffold(
        backgroundColor: Colors.black,
        body: SafeArea(
          child: Column(
            children: [
              AppBar(
                backgroundColor: Colors.black,
                leading: IconButton(
                  icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
                  onPressed: _onExit,
                ),
                title: Text(
                  lesson.title,
                  style: const TextStyle(color: Colors.white, fontSize: 16),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Expanded(
                child: Center(
                  child: _buildVideoArea(lesson),
                ),
              ),
              if (lesson.nextLesson != null || lesson.hasQuiz)
                Container(
                  padding: const EdgeInsets.all(16),
                  color: Colors.black87,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      if (lesson.hasQuiz)
                        FilledButton.icon(
                          onPressed: _checkQuizAndNavigate,
                          icon: const Icon(Icons.quiz_rounded),
                          label: const Text('اختبار الدرس'),
                          style: FilledButton.styleFrom(backgroundColor: AppTheme.primary),
                        ),
                      if (lesson.hasQuiz && lesson.nextLesson != null) const SizedBox(width: 12),
                      if (lesson.nextLesson != null)
                        TextButton.icon(
                          onPressed: _onExit,
                          icon: const Icon(Icons.check_circle_outline_rounded, color: Colors.white70),
                          label: Text('إنهاء والمتابعة', style: TextStyle(color: Colors.white70)),
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

  Widget _buildVideoArea(LessonDetailItem lesson) {
    if (lesson.videoUrl == null || lesson.videoUrl!.isEmpty) {
      return Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.play_circle_outline_rounded, size: 80, color: Colors.white54),
          const SizedBox(height: 16),
          Text(
            'لا يوجد فيديو لهذا الدرس',
            style: TextStyle(color: Colors.white70, fontSize: 16),
          ),
        ],
      );
    }

    if (_isYoutube && _youtubeController != null) {
      return YoutubePlayer(
        controller: _youtubeController!,
        showVideoProgressIndicator: true,
        progressIndicatorColor: AppTheme.primary,
      );
    }

    if (_videoController != null) {
      if (!_videoController!.value.isInitialized) {
        return const CircularProgressIndicator(color: Colors.white);
      }
      return AspectRatio(
        aspectRatio: _videoController!.value.aspectRatio,
        child: VideoPlayer(_videoController!),
      );
    }

    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.link_rounded, size: 64, color: Colors.white54),
        const SizedBox(height: 16),
        OutlinedButton.icon(
          onPressed: () {
            final url = lesson.videoUrl!;
            final u = url.startsWith('http') ? url : _fullUrl(url);
            launchUrl(Uri.parse(u), mode: LaunchMode.externalApplication);
          },
          icon: const Icon(Icons.open_in_new_rounded),
          label: const Text('فتح الفيديو'),
          style: OutlinedButton.styleFrom(foregroundColor: Colors.white),
        ),
      ],
    );
  }
}

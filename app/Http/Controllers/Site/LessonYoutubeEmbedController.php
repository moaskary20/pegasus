<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonAccessService;
use App\Support\YoutubeIframe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * صفحة تضمين يوتيوب منفصلة حتى لا يظهر رابط youtube في مصدر صفحة الدرس.
 */
class LessonYoutubeEmbedController extends Controller
{
    public function __invoke(Request $request, Course $course, Lesson $lesson, LessonAccessService $access): Response
    {
        abort_unless((bool) $course->is_published, 404);
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);
        abort_unless($lesson->isYoutubeVideo(), 404);

        $isDesignatedPreviewLesson = (int) ($course->preview_lesson_id ?? 0) === (int) $lesson->id;
        if (! $isDesignatedPreviewLesson) {
            abort_unless($access->canStreamLessonVideo($request->user(), $course, $lesson), 403);
        }

        $src = YoutubeIframe::embedSrcFromVideoId($lesson->getYoutubeVideoId(), true);
        abort_unless(filled($src), 404);

        $title = e($lesson->title ?? 'Lesson');
        $srcEsc = e($src);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>{$title}</title>
<style>
html,body{margin:0;height:100%;background:#0f172a;overflow:hidden}
iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.cover{position:absolute;pointer-events:auto;z-index:1}
.t{inset-inline:0;top:0;height:5.75rem;background:linear-gradient(to bottom,#000 30%,rgba(0,0,0,.8),transparent)}
.tr{right:0;top:0;width:12rem;height:5.5rem;background:linear-gradient(to bottom left,rgba(0,0,0,.9),rgba(0,0,0,.45),transparent)}
.br{right:0;bottom:0;width:13rem;height:6.5rem;background:linear-gradient(to top left,rgba(0,0,0,.9),rgba(0,0,0,.5),transparent)}
</style>
</head>
<body>
<iframe src="{$srcEsc}" title="{$title}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
<div class="cover t" aria-hidden="true"></div>
<div class="cover tr" aria-hidden="true"></div>
<div class="cover br" aria-hidden="true"></div>
</body>
</html>
HTML;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}

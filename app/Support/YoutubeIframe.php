<?php

namespace App\Support;

/**
 * معاملات مشغّل YouTube المضمّن: شريط تحكم مرئي (تشغيل، صوت، تقدّم) مع تقليل العلامة والفيديوهات ذات الصلة قدر ما تسمح به واجهة البرمجة.
 */
final class YoutubeIframe
{
    /**
     * @return string|null رابط iframe كامل أو null إن لم يُستخرج معرّف الفيديو
     */
    public static function embedSrcFromVideoId(?string $videoId, bool $autoplay = true): ?string
    {
        $videoId = trim((string) $videoId);
        if ($videoId === '' || strlen($videoId) !== 11) {
            return null;
        }

        $params = [
            // 1 = إظهار شريط التحكم (تشغيل، صوت، تقدّم الفيديو). 0 يخفيه بالكامل.
            'controls' => 1,
            'fs' => 1,
            'modestbranding' => 1,
            'rel' => 0,
            'iv_load_policy' => 3,
            'playsinline' => 1,
        ];
        if ($autoplay) {
            $params['autoplay'] = 1;
        }
        $origin = rtrim((string) config('app.url'), '/');
        if ($origin !== '') {
            $params['origin'] = $origin;
        }

        return 'https://www.youtube.com/embed/'.rawurlencode($videoId).'?'.http_build_query($params);
    }
}

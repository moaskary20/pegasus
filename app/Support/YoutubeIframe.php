<?php

namespace App\Support;

/**
 * معاملات مشغّل YouTube المضمّن لتقليل عناصر واجهة اليوتيوب (قائمة ⋮، شعار، فيديوهات ذات صلة من قنوات أخرى) قدر ما تسمح به واجهة البرمجة.
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
            // إخفاء شريط تحكم اليوتيوب الافتراضي (يشمل أيقونات تؤدي لمشاركة / مشاهدة لاحقاً من داخل المشغّل)
            'controls' => 0,
            'fs' => 0,
            'modestbranding' => 1,
            'rel' => 0,
            'iv_load_policy' => 3,
            'playsinline' => 1,
            'disablekb' => 1,
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

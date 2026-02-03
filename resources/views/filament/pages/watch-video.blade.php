<x-filament-panels::page>
    @if($lesson && $video)
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold mb-4">{{ $lesson->title }}</h2>
                
                @if($lesson->description)
                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $lesson->description }}</p>
                @endif

                <div class="bg-gray-900 rounded-lg aspect-video flex items-center justify-center mb-4">
                    @if($video->hls_path)
                        <video 
                            id="video-player"
                            class="w-full h-full"
                            controls
                            preload="metadata"
                            data-hls-path="{{ $video->hls_path }}"
                            data-duration="{{ $video->duration_seconds }}"
                            data-progress-id="{{ $progress->id ?? null }}"
                        >
                            <source src="{{ $video->hls_path }}" type="application/x-mpegURL">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    @elseif($video->path)
                        <video 
                            id="video-player"
                            class="w-full h-full"
                            controls
                            preload="metadata"
                            data-path="{{ asset('storage/' . $video->path) }}"
                            data-duration="{{ $video->duration_seconds }}"
                            data-progress-id="{{ $progress->id ?? null }}"
                        >
                            <source src="{{ asset('storage/' . $video->path) }}" type="video/mp4">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    @else
                        <p class="text-white">الفيديو غير متوفر حالياً</p>
                    @endif
                </div>

                @if($progress)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p>آخر موضع: {{ gmdate('H:i:s', $progress->last_position_seconds) }}</p>
                        @if($progress->completed)
                            <span class="inline-flex items-center gap-1 text-success-600 dark:text-success-400">
                                <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                                مكتمل
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            @if($lesson->files->count() > 0)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">الملفات المرفقة</h3>
                    <div class="space-y-2">
                        @foreach($lesson->files as $file)
                            <a href="{{ asset('storage/' . $file->path) }}" 
                               target="_blank"
                               class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
                                <x-filament::icon 
                                    icon="heroicon-o-document" 
                                    class="h-5 w-5 text-gray-500"
                                />
                                <span>{{ $file->name }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($file->size / 1024, 1) }} KB)</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($lesson->quiz)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">اختبار المحاضرة</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $lesson->quiz->title }}</p>
                    <a href="{{ \App\Filament\Pages\TakeQuiz::getUrl(['quiz' => $lesson->quiz->id]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-5 w-5" />
                        أداء الاختبار
                    </a>
                </div>
            @endif
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const video = document.getElementById('video-player');
                    if (!video) return;

                    const hlsPath = video.dataset.hlsPath;
                    const progressId = video.dataset.progressId;
                    const duration = parseInt(video.dataset.duration || 0);

                    // Load HLS if available
                    if (hlsPath && typeof Hls !== 'undefined') {
                        const hls = new Hls();
                        hls.loadSource(hlsPath);
                        hls.attachMedia(video);
                    } else if (hlsPath && video.canPlayType('application/vnd.apple.mpegurl')) {
                        // Native HLS support (Safari)
                        video.src = hlsPath;
                    }

                    // Resume from last position
                    @if($progress && $progress->last_position_seconds > 0)
                        video.currentTime = {{ $progress->last_position_seconds }};
                    @endif

                    // Save progress periodically
                    let saveTimeout;
                    video.addEventListener('timeupdate', function() {
                        clearTimeout(saveTimeout);
                        saveTimeout = setTimeout(function() {
                            if (progressId && duration > 0) {
                                @this.call('saveProgress', Math.floor(video.currentTime), duration);
                            }
                        }, 5000); // Save every 5 seconds
                    });

                    // Mark as completed when video ends
                    video.addEventListener('ended', function() {
                        if (progressId && duration > 0) {
                            @this.call('saveProgress', duration, duration);
                        }
                    });
                });
            </script>
        @endpush
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">الفيديو غير متوفر</p>
        </div>
    @endif
</x-filament-panels::page>

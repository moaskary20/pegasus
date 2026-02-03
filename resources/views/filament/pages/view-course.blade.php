<x-filament-panels::page>
    @if($course)
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex gap-6">
                    @if($course->cover_image)
                        <img src="{{ asset('storage/' . $course->cover_image) }}" 
                             alt="{{ $course->title }}" 
                             class="w-32 h-32 object-cover rounded-lg">
                    @endif
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold mb-2">{{ $course->title }}</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $course->description }}</p>
                        <div class="flex gap-4 text-sm">
                            <span><strong>المدرس:</strong> {{ $course->user->name }}</span>
                            <span><strong>المستوى:</strong> 
                                @if($course->level === 'beginner') مبتدئ
                                @elseif($course->level === 'intermediate') متوسط
                                @else متقدم
                                @endif
                            </span>
                            <span><strong>عدد الساعات:</strong> {{ $course->hours }} ساعة</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($enrollment)
                <div class="rounded-lg bg-primary-50 dark:bg-primary-900/20 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-primary-900 dark:text-primary-100">
                                تقدمك في الدورة
                            </p>
                            <p class="text-sm text-primary-700 dark:text-primary-300">
                                {{ number_format($enrollment->progress_percentage, 1) }}% مكتمل
                            </p>
                        </div>
                        <div class="w-48">
                            <div class="h-2 bg-primary-200 dark:bg-primary-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 dark:bg-primary-400 rounded-full transition-all"
                                     style="width: {{ $enrollment->progress_percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <h3 class="text-lg font-semibold mb-4">محتوى الدورة</h3>
                {{ $this->table }}
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">لم يتم تحديد دورة</p>
        </div>
    @endif
</x-filament-panels::page>

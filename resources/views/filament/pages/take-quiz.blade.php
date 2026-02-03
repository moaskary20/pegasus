<x-filament-panels::page>
    @if($quiz)
        <div class="space-y-6">
            @if($submitted)
                <div class="rounded-lg p-6 {{ $passed ? 'bg-success-50 dark:bg-success-900/20' : 'bg-warning-50 dark:bg-warning-900/20' }}">
                    <div class="flex items-center gap-3">
                        <x-filament::icon
                            icon="{{ $passed ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle' }}"
                            class="h-8 w-8 {{ $passed ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}"
                        />
                        <div>
                            <h3 class="text-lg font-semibold {{ $passed ? 'text-success-900 dark:text-success-100' : 'text-warning-900 dark:text-warning-100' }}">
                                {{ $passed ? 'تهانينا! نجحت في الاختبار' : 'لم تنجح في الاختبار' }}
                            </h3>
                            <p class="text-sm {{ $passed ? 'text-success-700 dark:text-success-300' : 'text-warning-700 dark:text-warning-300' }}">
                                نقاطك: <strong>{{ number_format($score, 1) }}%</strong> (المطلوب: {{ $quiz->pass_percentage }}%)
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($quiz->description)
                <div class="prose dark:prose-invert max-w-none">
                    <p>{{ $quiz->description }}</p>
                </div>
            @endif

            @if($quiz->duration_minutes && !$submitted)
                <div class="rounded-lg {{ $timeExpired ? 'bg-danger-50 dark:bg-danger-900/20' : 'bg-warning-50 dark:bg-warning-900/20' }} p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold {{ $timeExpired ? 'text-danger-700 dark:text-danger-300' : 'text-warning-700 dark:text-warning-300' }}">
                            <strong>المدة:</strong> {{ $quiz->duration_minutes }} دقيقة
                        </p>
                        @if($timeRemaining !== null && !$timeExpired)
                            <div class="flex items-center gap-2">
                                <x-filament::icon 
                                    icon="heroicon-o-clock" 
                                    class="h-5 w-5 {{ $timeRemaining < 300 ? 'text-danger-600' : 'text-warning-600' }}"
                                />
                                <span id="timer" 
                                      class="text-lg font-bold {{ $timeRemaining < 300 ? 'text-danger-600 dark:text-danger-400' : 'text-warning-600 dark:text-warning-400' }}"
                                      data-remaining="{{ $timeRemaining }}">
                                    {{ gmdate('H:i:s', $timeRemaining) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            @if($timeExpired)
                <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 p-4 mb-4">
                    <p class="text-danger-700 dark:text-danger-300 font-semibold">
                        ⏰ انتهى الوقت! تم إرسال إجاباتك تلقائياً.
                    </p>
                </div>
            @endif

            @if(!$submitted)
                <form wire:submit="submit">
                    {{ $this->form }}
                    
                    <div class="mt-6 flex gap-3">
                        <x-filament::button type="submit" size="lg">
                            إرسال الإجابات
                        </x-filament::button>
                    </div>
                </form>
            @else
                <div class="space-y-6">
                    @foreach($quiz->questions as $question)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <h4 class="font-semibold mb-2">{{ $question->question_text }}</h4>
                            
                            @php
                                $userAnswer = $answers[$question->id] ?? null;
                                $correctAnswer = $question->correct_answer;
                                $isCorrect = false;
                                
                                switch($question->type) {
                                    case 'mcq':
                                    case 'true_false':
                                        $isCorrect = is_array($correctAnswer) && in_array($userAnswer, $correctAnswer);
                                        break;
                                    case 'fill_blank':
                                        if (is_array($correctAnswer)) {
                                            $userAnswerLower = strtolower(trim($userAnswer ?? ''));
                                            foreach ($correctAnswer as $correct) {
                                                if (strtolower(trim($correct)) === $userAnswerLower) {
                                                    $isCorrect = true;
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                }
                            @endphp
                            
                            <div class="mt-2 space-y-2">
                                <p class="text-sm">
                                    <strong>إجابتك:</strong> 
                                    <span class="{{ $isCorrect ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                        {{ $userAnswer ?? 'لم تجب' }}
                                    </span>
                                </p>
                                
                                @if(!$isCorrect)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>الإجابة الصحيحة:</strong> {{ is_array($correctAnswer) ? implode(', ', $correctAnswer) : $correctAnswer }}
                                    </p>
                                @endif
                                
                                @if($question->explanation)
                                    <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded">
                                        <p class="text-sm"><strong>التفسير:</strong> {{ $question->explanation }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        @if($timeRemaining !== null && !$submitted && !$timeExpired)
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const timerElement = document.getElementById('timer');
                        if (!timerElement) return;
                        
                        let remaining = parseInt(timerElement.dataset.remaining);
                        
                        const interval = setInterval(function() {
                            remaining--;
                            
                            if (remaining <= 0) {
                                clearInterval(interval);
                                timerElement.textContent = '00:00:00';
                                timerElement.classList.add('text-danger-600');
                                
                                // Auto submit
                                @this.call('autoSubmit');
                            } else {
                                const hours = Math.floor(remaining / 3600);
                                const minutes = Math.floor((remaining % 3600) / 60);
                                const seconds = remaining % 60;
                                
                                timerElement.textContent = 
                                    String(hours).padStart(2, '0') + ':' +
                                    String(minutes).padStart(2, '0') + ':' +
                                    String(seconds).padStart(2, '0');
                                
                                // Change color when less than 5 minutes
                                if (remaining < 300) {
                                    timerElement.classList.remove('text-warning-600', 'text-warning-400');
                                    timerElement.classList.add('text-danger-600', 'text-danger-400');
                                }
                            }
                        }, 1000);
                    });
                </script>
            @endpush
        @endif
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">لم يتم تحديد اختبار</p>
        </div>
    @endif
</x-filament-panels::page>

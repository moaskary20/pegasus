<div class="space-y-4">
    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
        <h3 class="text-sm font-semibold text-orange-900 dark:text-orange-100 mb-2">طرح سؤال جديد</h3>
        <form wire:submit.prevent="addQuestion" class="space-y-3">
            <textarea 
                wire:model="newQuestion"
                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                rows="3"
                placeholder="اكتب سؤالك هنا..."></textarea>
            <button 
                type="submit"
                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-colors">
                طرح السؤال
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($questions as $question)
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 bg-orange-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($question->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $question->user->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $question->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($question->is_answered)
                        <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 rounded text-xs font-medium">
                            تم الرد
                        </span>
                    @else
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 rounded text-xs font-medium">
                            في انتظار الرد
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $question->question }}</p>
                
                @if($question->answers->count() > 0)
                    <div class="mr-8 mt-3 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-3">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">الردود:</p>
                        @foreach($question->answers as $answer)
                            <div class="bg-green-50 dark:bg-green-900/20 rounded p-3 border border-green-200 dark:border-green-800">
                                <div class="flex items-center space-x-2 space-x-reverse mb-1">
                                    <span class="text-xs font-semibold text-green-700 dark:text-green-300">{{ $answer->user->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $answer->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-700 dark:text-gray-300">{{ $answer->answer }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>لا توجد أسئلة بعد</p>
            </div>
        @endforelse
    </div>
</div>

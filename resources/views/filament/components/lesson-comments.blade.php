<div class="space-y-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">إضافة تعليق جديد</h3>
        <form wire:submit.prevent="addComment" class="space-y-3">
            <textarea 
                wire:model="newComment"
                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                rows="3"
                placeholder="اكتب تعليقك هنا..."></textarea>
            <button 
                type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                إضافة تعليق
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $comment->body }}</p>
                
                @if($comment->replies->count() > 0)
                    <div class="mr-8 mt-3 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-3">
                        @foreach($comment->replies as $reply)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded p-2">
                                <div class="flex items-center space-x-2 space-x-reverse mb-1">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $reply->user->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $reply->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>لا توجد تعليقات بعد</p>
            </div>
        @endforelse
    </div>
</div>

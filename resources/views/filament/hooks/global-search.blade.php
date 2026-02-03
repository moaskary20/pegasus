<div 
    x-data="{
        open: false,
        query: '',
        loading: false,
        results: null,
        
        async search() {
            if (this.query.length < 2) {
                this.results = null;
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch(`/api/search/results?q=${encodeURIComponent(this.query)}`);
                const data = await response.json();
                this.results = data;
            } catch (e) {
                console.error('Search error:', e);
            }
            
            this.loading = false;
        },
        
        goToAdvancedSearch() {
            window.location.href = '/admin/search?query=' + encodeURIComponent(this.query);
            this.open = false;
        },
        
        handleKeydown(e) {
            if (e.key === 'Escape') {
                this.open = false;
            }
            if (e.key === 'Enter' && this.query.length >= 2) {
                this.goToAdvancedSearch();
            }
        }
    }"
    @keydown.window.ctrl.k.prevent="open = true; $nextTick(() => $refs.searchInput?.focus())"
    @keydown.window.cmd.k.prevent="open = true; $nextTick(() => $refs.searchInput?.focus())"
    class="relative"
>
    {{-- Search Trigger Button --}}
    <button
        @click="open = true; $nextTick(() => $refs.searchInput?.focus())"
        class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700"
    >
        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
        <span class="hidden sm:inline">بحث</span>
        <kbd class="hidden md:inline px-1.5 py-0.5 text-xs bg-white dark:bg-gray-900 rounded border border-gray-300 dark:border-gray-600">⌘K</kbd>
    </button>
    
    {{-- Search Modal --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @click.self="open = false"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-start justify-center pt-20 bg-black/50"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Search Input --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                    <input
                        x-ref="searchInput"
                        x-model="query"
                        @input.debounce.300ms="search"
                        @keydown="handleKeydown"
                        type="text"
                        placeholder="ابحث في الدورات، الدروس، المدرسين..."
                        class="flex-1 border-0 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 focus:ring-0 text-lg"
                    />
                    <template x-if="loading">
                        <svg class="w-5 h-5 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                
                {{-- Quick Results --}}
                <div x-show="results && results.total > 0" class="max-h-96 overflow-y-auto">
                    {{-- Courses --}}
                    <template x-if="results?.results?.courses?.length > 0">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">الدورات</h4>
                            <div class="space-y-2">
                                <template x-for="course in results.results.courses.slice(0, 3)" :key="course.id">
                                    <a 
                                        :href="'/admin/view-course/' + course.id"
                                        @click="open = false"
                                        class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <x-heroicon-o-academic-cap class="w-5 h-5 text-primary-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="course.title"></p>
                                            <p class="text-xs text-gray-500" x-text="course.instructor"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                    
                    {{-- Instructors --}}
                    <template x-if="results?.results?.instructors?.length > 0">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">المدرسون</h4>
                            <div class="space-y-2">
                                <template x-for="instructor in results.results.instructors.slice(0, 3)" :key="instructor.id">
                                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                            <x-heroicon-o-user class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="instructor.name"></p>
                                            <p class="text-xs text-gray-500" x-text="instructor.courses_count + ' دورة'"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                
                {{-- No Results --}}
                <div x-show="results && results.total === 0" class="p-8 text-center">
                    <x-heroicon-o-magnifying-glass class="w-12 h-12 mx-auto text-gray-300" />
                    <p class="mt-2 text-gray-500">لا توجد نتائج</p>
                </div>
                
                {{-- Footer --}}
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600">Enter</kbd>
                        للبحث المتقدم
                    </div>
                    <button
                        @click="goToAdvancedSearch"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                    >
                        البحث المتقدم ←
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

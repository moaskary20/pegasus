@props([
    'placeholder' => 'ابحث...',
    'minChars' => 2,
    'debounce' => 300,
])

<div 
    x-data="{
        query: '',
        isOpen: false,
        suggestions: [],
        selectedIndex: -1,
        loading: false,
        
        async fetchSuggestions() {
            if (this.query.length < {{ $minChars }}) {
                this.suggestions = [];
                this.isOpen = false;
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(this.query)}`);
                const data = await response.json();
                
                this.suggestions = [
                    ...(data.courses || []).map(c => ({ type: 'course', text: c })),
                    ...(data.recent || []).map(r => ({ type: 'recent', text: r })),
                    ...(data.popular || []).map(p => ({ type: 'popular', text: p })),
                ];
                
                this.isOpen = this.suggestions.length > 0;
            } catch (e) {
                console.error('Search suggestions error:', e);
            }
            
            this.loading = false;
        },
        
        selectSuggestion(suggestion) {
            this.query = suggestion.text;
            this.isOpen = false;
            this.$refs.input.form?.submit();
        },
        
        handleKeydown(e) {
            if (!this.isOpen) return;
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    break;
                case 'Enter':
                    if (this.selectedIndex >= 0) {
                        e.preventDefault();
                        this.selectSuggestion(this.suggestions[this.selectedIndex]);
                    }
                    break;
                case 'Escape':
                    this.isOpen = false;
                    this.selectedIndex = -1;
                    break;
            }
        },
        
        getIcon(type) {
            switch(type) {
                case 'course': return 'academic-cap';
                case 'recent': return 'clock';
                case 'popular': return 'fire';
                default: return 'magnifying-glass';
            }
        }
    }"
    x-init="$watch('query', () => { 
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => fetchSuggestions(), {{ $debounce }});
    })"
    class="relative"
    @click.away="isOpen = false"
>
    <div class="relative">
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <template x-if="loading">
                <svg class="w-5 h-5 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            <template x-if="!loading">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </template>
        </div>
        
        <input
            x-ref="input"
            x-model="query"
            @keydown="handleKeydown"
            @focus="query.length >= {{ $minChars }} && (isOpen = true)"
            type="text"
            name="q"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            {{ $attributes->merge(['class' => 'w-full pr-10 pl-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500']) }}
        />
        
        <button
            x-show="query.length > 0"
            x-cloak
            @click="query = ''; isOpen = false; suggestions = [];"
            type="button"
            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 hover:text-gray-600"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    {{-- Suggestions Dropdown --}}
    <div
        x-show="isOpen && suggestions.length > 0"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
    >
        <ul class="max-h-60 overflow-y-auto py-2">
            <template x-for="(suggestion, index) in suggestions" :key="index">
                <li>
                    <button
                        type="button"
                        @click="selectSuggestion(suggestion)"
                        @mouseenter="selectedIndex = index"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedIndex === index }"
                        class="w-full flex items-center gap-3 px-4 py-2 text-right hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <span class="flex-shrink-0 text-gray-400">
                            <template x-if="suggestion.type === 'course'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                </svg>
                            </template>
                            <template x-if="suggestion.type === 'recent'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                            <template x-if="suggestion.type === 'popular'">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                </svg>
                            </template>
                        </span>
                        <span class="flex-1 text-gray-900 dark:text-white" x-text="suggestion.text"></span>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>

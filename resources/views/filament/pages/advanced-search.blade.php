<x-filament-panels::page>
    <style>
        .search-container { max-width: 100%; }
        
        .search-header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid #374151;
            margin-bottom: 20px;
        }
        
        .search-input-section {
            padding: 20px;
            display: flex;
            gap: 12px;
        }
        .search-input-wrapper {
            flex: 1;
            position: relative;
        }
        .search-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
        .search-input {
            width: 100%;
            padding: 14px 44px 14px 40px;
            border: none;
            border-radius: 12px;
            background: rgba(55, 65, 81, 0.8);
            color: white;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .search-input::placeholder { color: #6b7280; }
        .search-input:focus { background: #374151; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3); }
        .clear-btn {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
        }
        .clear-btn:hover { color: #9ca3af; }
        .search-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .search-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4); }
        
        .filters-section {
            padding: 12px 20px;
            background: rgba(55, 65, 81, 0.5);
            border-top: 1px solid rgba(75, 85, 99, 0.5);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filters-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #9ca3af;
            font-size: 12px;
            font-weight: 500;
        }
        .filters-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            flex: 1;
        }
        .filter-select {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            background: white;
            color: #374151;
            font-size: 12px;
            cursor: pointer;
            outline: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-select:focus { box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3); }
        .clear-filters-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            background: none;
            border: none;
            border-radius: 6px;
            color: #ef4444;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .clear-filters-btn:hover { background: rgba(239, 68, 68, 0.1); }
        
        .suggestions-section {
            padding: 12px 20px;
            background: rgba(55, 65, 81, 0.3);
            border-top: 1px solid rgba(75, 85, 99, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .suggestions-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .suggestions-label {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #6b7280;
            font-size: 11px;
        }
        .suggestion-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .suggestion-btn.recent { background: rgba(75, 85, 99, 0.5); color: #d1d5db; }
        .suggestion-btn.recent:hover { background: rgba(75, 85, 99, 0.8); color: white; }
        .suggestion-btn.popular { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .suggestion-btn.popular:hover { background: rgba(245, 158, 11, 0.2); }
        .clear-history-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 14px;
        }
        .clear-history-btn:hover { color: #ef4444; }
        .suggestions-divider {
            width: 1px;
            height: 20px;
            background: #4b5563;
            margin: 0 8px;
        }
        
        .results-container {
            background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border: 1px solid #374151;
        }
        
        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: rgba(55, 65, 81, 0.3);
            border-bottom: 1px solid rgba(75, 85, 99, 0.5);
        }
        .tabs-nav { display: flex; gap: 6px; }
        .tab-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: #9ca3af;
        }
        .tab-btn:hover { color: #d1d5db; background: rgba(75, 85, 99, 0.5); }
        .tab-btn.active { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }
        .tab-count {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }
        .tab-btn.active .tab-count { background: rgba(99, 102, 241, 0.3); color: #c7d2fe; }
        .tab-btn:not(.active) .tab-count { background: rgba(75, 85, 99, 0.5); color: #6b7280; }
        .results-info {
            font-size: 12px;
            color: #6b7280;
        }
        .results-info .count { color: #9ca3af; }
        .results-info .query { color: #a5b4fc; font-weight: 500; }
        
        .results-body { padding: 20px; }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }
        .course-card {
            background: rgba(55, 65, 81, 0.5);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(75, 85, 99, 0.5);
            transition: all 0.3s;
        }
        .course-card:hover {
            border-color: rgba(75, 85, 99, 0.8);
            background: rgba(55, 65, 81, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .course-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }
        .course-image-placeholder {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.6), rgba(139, 92, 246, 0.6));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .course-free-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            background: #22c55e;
            color: white;
            font-size: 10px;
            font-weight: 600;
            border-radius: 6px;
        }
        .course-body { padding: 14px; }
        .course-title {
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin: 0 0 8px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .course-card:hover .course-title { color: #a5b4fc; }
        .course-instructor {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .course-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .course-stats { display: flex; gap: 10px; }
        .course-stat {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .course-stat.rating { color: #fbbf24; }
        .course-stat.students { color: #6b7280; }
        .course-price { font-weight: 700; color: #a5b4fc; }
        .course-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .course-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .lessons-list, .questions-list { display: flex; flex-direction: column; gap: 8px; }
        .list-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            background: rgba(55, 65, 81, 0.4);
            border-radius: 12px;
            border: 1px solid rgba(75, 85, 99, 0.3);
            transition: all 0.2s;
        }
        .list-item:hover {
            background: rgba(55, 65, 81, 0.6);
            border-color: rgba(75, 85, 99, 0.5);
        }
        .list-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .list-icon.lesson { background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3)); }
        .list-icon.question { background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(99, 102, 241, 0.3)); }
        .list-icon:hover { background: linear-gradient(135deg, rgba(99, 102, 241, 0.5), rgba(139, 92, 246, 0.5)); }
        .list-content { flex: 1; min-width: 0; }
        .list-title {
            font-size: 13px;
            font-weight: 500;
            color: white;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .list-item:hover .list-title { color: #a5b4fc; }
        .list-meta {
            display: flex;
            gap: 14px;
            margin-top: 4px;
            font-size: 11px;
            color: #6b7280;
        }
        .list-meta-item { display: flex; align-items: center; gap: 4px; }
        .list-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 500;
        }
        .list-badge.free { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .list-badge.answers { color: #4ade80; }
        
        .instructors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        .instructor-card {
            text-align: center;
            padding: 20px 14px;
            background: rgba(55, 65, 81, 0.4);
            border-radius: 12px;
            border: 1px solid rgba(75, 85, 99, 0.3);
            transition: all 0.2s;
        }
        .instructor-card:hover {
            background: rgba(55, 65, 81, 0.6);
            border-color: rgba(75, 85, 99, 0.5);
        }
        .instructor-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto 10px;
            object-fit: cover;
            border: 3px solid #374151;
            transition: border-color 0.2s;
        }
        .instructor-card:hover .instructor-avatar { border-color: rgba(99, 102, 241, 0.5); }
        .instructor-avatar-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #374151;
        }
        .instructor-name {
            font-size: 12px;
            font-weight: 500;
            color: white;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .instructor-card:hover .instructor-name { color: #a5b4fc; }
        .instructor-courses {
            font-size: 11px;
            color: #a5b4fc;
            margin-top: 4px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-icon {
            width: 70px;
            height: 70px;
            background: rgba(55, 65, 81, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .empty-text { font-size: 14px; color: #6b7280; margin: 0; }
        
        .min-chars-message {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%);
            border-radius: 16px;
            border: 1px solid #374151;
        }
    </style>

    <div class="search-container">
        {{-- Search Header --}}
        <div class="search-header">
            <div class="search-input-section">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        class="search-input"
                        wire:model.live.debounce.300ms="query"
                        wire:keydown.enter="search"
                        placeholder="ابحث عن دورات، دروس، مدرسين..."
                    />
                    @if($query)
                        <button class="clear-btn" wire:click="clearSearch">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
                <button class="search-btn" wire:click="search">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    بحث
                </button>
            </div>
            
            <div class="filters-section">
                <div class="filters-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    الفلاتر
                </div>
                
                <div class="filters-wrapper">
                    <select class="filter-select" wire:model.live="categoryId" wire:change="search">
                        <option value="">التصنيف: الكل</option>
                        @foreach($this->getCategories() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    
                    <select class="filter-select" wire:model.live="level" wire:change="search">
                        <option value="">المستوى: الكل</option>
                        @foreach($this->getLevels() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    
                    <select class="filter-select" wire:model.live="priceType" wire:change="search">
                        <option value="">السعر: الكل</option>
                        <option value="free">مجاني</option>
                        <option value="paid">مدفوع</option>
                    </select>
                    
                    <select class="filter-select" wire:model.live="minRating" wire:change="search">
                        <option value="">التقييم: الكل</option>
                        @foreach($this->getRatings() as $value => $label)
                            @if($value)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                    
                    <select class="filter-select" wire:model.live="instructorId" wire:change="search">
                        <option value="">المدرس: الكل</option>
                        @foreach($this->getInstructors() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    
                    <select class="filter-select" wire:model.live="sort" wire:change="search">
                        @foreach($this->getSortOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <button class="clear-filters-btn" wire:click="clearFilters">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    مسح
                </button>
            </div>
            
            @if(!$hasSearched && (isset($suggestions['recent']) && count($suggestions['recent']) > 0 || isset($suggestions['popular']) && count($suggestions['popular']) > 0))
                <div class="suggestions-section">
                    @if(isset($suggestions['recent']) && count($suggestions['recent']) > 0)
                        <div class="suggestions-group">
                            <span class="suggestions-label">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                الأخيرة:
                            </span>
                            @foreach($suggestions['recent'] as $suggestion)
                                <button class="suggestion-btn recent" wire:click="searchFromSuggestion('{{ $suggestion }}')">{{ $suggestion }}</button>
                            @endforeach
                            <button class="clear-history-btn" wire:click="clearHistory">×</button>
                        </div>
                    @endif
                    
                    @if(isset($suggestions['popular']) && count($suggestions['popular']) > 0)
                        @if(isset($suggestions['recent']) && count($suggestions['recent']) > 0)
                            <div class="suggestions-divider"></div>
                        @endif
                        <div class="suggestions-group">
                            <span class="suggestions-label" style="color: #fbbf24;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                </svg>
                                الشائعة:
                            </span>
                            @foreach($suggestions['popular'] as $suggestion)
                                <button class="suggestion-btn popular" wire:click="searchFromSuggestion('{{ $suggestion }}')">{{ $suggestion }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
        
        {{-- Results --}}
        @if($hasSearched)
            <div class="results-container">
                <div class="results-header">
                    <nav class="tabs-nav">
                        @php
                            $tabs = [
                                'courses' => ['label' => 'الدورات', 'icon' => 'M12 14l9-5-9-5-9 5 9 5z'],
                                'lessons' => ['label' => 'الدروس', 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z'],
                                'instructors' => ['label' => 'المدرسون', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                                'questions' => ['label' => 'الأسئلة', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ];
                        @endphp
                        @foreach($tabs as $key => $tab)
                            <button class="tab-btn {{ $activeTab === $key ? 'active' : '' }}" wire:click="setActiveTab('{{ $key }}')">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                                </svg>
                                {{ $tab['label'] }}
                                <span class="tab-count">{{ $this->getResultsCount($key) }}</span>
                            </button>
                        @endforeach
                    </nav>
                    <div class="results-info">
                        <span class="count">{{ $totalResults }}</span> نتيجة لـ "<span class="query">{{ $query }}</span>"
                    </div>
                </div>
                
                <div class="results-body">
                    {{-- Courses Tab --}}
                    @if($activeTab === 'courses')
                        @if(isset($results['courses']) && $results['courses']->count() > 0)
                            <div class="courses-grid">
                                @foreach($results['courses'] as $course)
                                    <div class="course-card">
                                        <div style="position: relative;">
                                            @if($course['cover_image'])
                                                <img src="{{ asset('storage/' . $course['cover_image']) }}" alt="{{ $course['title'] }}" class="course-image" />
                                            @else
                                                <div class="course-image-placeholder">
                                                    <svg width="32" height="32" fill="none" stroke="rgba(255,255,255,0.3)" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            @if($course['price'] == 0)
                                                <span class="course-free-badge">مجاني</span>
                                            @endif
                                        </div>
                                        <div class="course-body">
                                            <h4 class="course-title">{{ $course['title'] }}</h4>
                                            <p class="course-instructor">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                {{ $course['instructor'] }}
                                            </p>
                                            <div class="course-meta">
                                                <div class="course-stats">
                                                    <span class="course-stat rating">
                                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        {{ number_format($course['rating'], 1) }}
                                                    </span>
                                                    <span class="course-stat students">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        {{ $course['students_count'] }}
                                                    </span>
                                                </div>
                                                @if($course['price'] > 0)
                                                    <span class="course-price">{{ number_format($course['price'], 0) }} ج.م</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('filament.admin.pages.view-course', ['course' => $course['id']]) }}" class="course-btn">عرض الدورة</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="28" height="28" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    </svg>
                                </div>
                                <p class="empty-text">لا توجد دورات مطابقة لبحثك</p>
                            </div>
                        @endif
                    @endif
                    
                    {{-- Lessons Tab --}}
                    @if($activeTab === 'lessons')
                        @if(isset($results['lessons']) && $results['lessons']->count() > 0)
                            <div class="lessons-list">
                                @foreach($results['lessons'] as $lesson)
                                    <div class="list-item">
                                        <div class="list-icon lesson">
                                            <svg width="18" height="18" fill="none" stroke="#a5b4fc" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            </svg>
                                        </div>
                                        <div class="list-content">
                                            <p class="list-title">{{ $lesson['title'] }}</p>
                                            <div class="list-meta">
                                                <span class="list-meta-item">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                    </svg>
                                                    {{ $lesson['course_title'] }}
                                                </span>
                                                @if($lesson['duration_minutes'])
                                                    <span class="list-meta-item">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $lesson['duration_minutes'] }} د
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($lesson['is_free'])
                                            <span class="list-badge free">مجاني</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="28" height="28" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    </svg>
                                </div>
                                <p class="empty-text">لا توجد دروس مطابقة لبحثك</p>
                            </div>
                        @endif
                    @endif
                    
                    {{-- Instructors Tab --}}
                    @if($activeTab === 'instructors')
                        @if(isset($results['instructors']) && $results['instructors']->count() > 0)
                            <div class="instructors-grid">
                                @foreach($results['instructors'] as $instructor)
                                    <div class="instructor-card">
                                        @if($instructor['avatar'])
                                            <img src="{{ asset('storage/' . $instructor['avatar']) }}" alt="{{ $instructor['name'] }}" class="instructor-avatar" />
                                        @else
                                            <div class="instructor-avatar-placeholder">
                                                <svg width="20" height="20" fill="none" stroke="#a5b4fc" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <p class="instructor-name">{{ $instructor['name'] }}</p>
                                        <p class="instructor-courses">{{ $instructor['courses_count'] }} دورة</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="28" height="28" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <p class="empty-text">لا يوجد مدرسون مطابقون لبحثك</p>
                            </div>
                        @endif
                    @endif
                    
                    {{-- Questions Tab --}}
                    @if($activeTab === 'questions')
                        @if(isset($results['questions']) && $results['questions']->count() > 0)
                            <div class="questions-list">
                                @foreach($results['questions'] as $question)
                                    <div class="list-item">
                                        <div class="list-icon question">
                                            <svg width="16" height="16" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="list-content">
                                            <p class="list-title" style="white-space: normal; -webkit-line-clamp: 2; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;">{{ $question['question'] }}</p>
                                            <div class="list-meta">
                                                <span class="list-meta-item">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                    </svg>
                                                    {{ $question['course_title'] }}
                                                </span>
                                                <span class="list-meta-item list-badge answers">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                                    </svg>
                                                    {{ $question['answers_count'] }} إجابة
                                                </span>
                                                <span>{{ $question['created_at'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="28" height="28" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="empty-text">لا توجد أسئلة مطابقة لبحثك</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @elseif(strlen($query) > 0 && strlen($query) < 2)
            <div class="min-chars-message">
                <div class="empty-icon">
                    <svg width="28" height="28" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="empty-text">أدخل حرفين على الأقل للبحث</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>

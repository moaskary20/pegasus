<x-filament-panels::page>
    <style>
        .assignment-view { display: grid; grid-template-columns: 1fr; gap: 24px; }
        
        .assignment-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        .assignment-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content { position: relative; z-index: 1; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
        .header-info { display: flex; align-items: center; gap: 16px; }
        .header-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .header-text h1 { font-size: 24px; font-weight: 800; margin: 0; }
        .header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        .header-meta { display: flex; gap: 20px; flex-wrap: wrap; }
        .meta-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
        }
        .meta-label { opacity: 0.8; }
        .meta-value { font-weight: 700; }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        .stat-box {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
        }
        .stat-value { font-size: 24px; font-weight: 800; }
        .stat-label { font-size: 11px; opacity: 0.9; margin-top: 4px; }
        
        .content-area {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .content-area { grid-template-columns: 1fr; }
        }
        
        .submissions-list {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
            max-height: 700px;
            display: flex;
            flex-direction: column;
        }
        .submissions-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .submissions-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .submissions-body { flex: 1; overflow-y: auto; }
        
        .submission-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }
        .submission-item:hover { background: #f9fafb; }
        .submission-item.active { background: #ede9fe; border-right: 3px solid #8b5cf6; }
        .submission-avatar {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        .submission-info { flex: 1; min-width: 0; }
        .submission-name { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .submission-meta { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        .submission-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .submission-status.submitted { background: #fef3c7; color: #92400e; }
        .submission-status.graded { background: #dcfce7; color: #166534; }
        .submission-status.returned { background: #dbeafe; color: #1e40af; }
        .submission-status.resubmit_requested { background: #fee2e2; color: #991b1b; }
        
        .detail-panel {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .detail-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detail-user { display: flex; align-items: center; gap: 14px; }
        .detail-avatar {
            width: 50px; height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }
        .detail-name { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
        .detail-email { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
        .detail-score {
            font-size: 32px;
            font-weight: 800;
            color: #8b5cf6;
        }
        .detail-score span { font-size: 16px; color: #9ca3af; font-weight: 500; }
        
        .detail-body { padding: 24px; }
        
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .files-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
        .file-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f9fafb;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .file-icon { font-size: 24px; }
        .file-info { flex: 1; }
        .file-name { font-size: 13px; font-weight: 600; color: #1f2937; }
        .file-size { font-size: 11px; color: #6b7280; }
        .file-download {
            background: #8b5cf6;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .file-download:hover { background: #7c3aed; }
        
        .content-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }
        
        .grading-section {
            background: linear-gradient(135deg, #faf5ff, #f3e8ff);
            border: 1px solid #e9d5ff;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .grading-title { font-size: 15px; font-weight: 700; color: #6b21a8; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; }
        .grading-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .grading-input-group { flex: 1; }
        .grading-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
        .grading-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9d5ff;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .grading-input:focus { outline: none; border-color: #8b5cf6; }
        .grading-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 16px;
            border: 2px solid #e9d5ff;
            border-radius: 10px;
            font-size: 14px;
            resize: vertical;
        }
        .grading-textarea:focus { outline: none; border-color: #8b5cf6; }
        .grading-actions { display: flex; gap: 12px; }
        .grade-btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .grade-btn.primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        .grade-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4); }
        .grade-btn.secondary { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        .grade-btn.secondary:hover { background: #e5e7eb; }
        
        .comments-section { border-top: 1px solid #e5e7eb; padding-top: 24px; }
        .comments-list { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }
        .comment-item { display: flex; gap: 12px; }
        .comment-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: #e5e7eb;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
        }
        .comment-content { flex: 1; background: #f9fafb; padding: 12px 16px; border-radius: 12px; }
        .comment-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .comment-name { font-size: 13px; font-weight: 600; color: #1f2937; }
        .comment-time { font-size: 11px; color: #9ca3af; }
        .comment-text { font-size: 13px; color: #374151; line-height: 1.6; }
        
        .add-comment { display: flex; gap: 12px; }
        .add-comment input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        .add-comment input:focus { outline: none; border-color: #8b5cf6; }
        .add-comment button {
            background: #8b5cf6;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        
        .empty-detail {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            text-align: center;
        }
        .empty-icon { font-size: 64px; margin-bottom: 16px; }
        .empty-title { font-size: 18px; font-weight: 600; color: #374151; margin: 0 0 8px 0; }
        .empty-text { font-size: 14px; color: #6b7280; }
        
        .late-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 8px;
        }
        
        @media (prefers-color-scheme: dark) {
            .submissions-list, .detail-panel { background: #1f2937; border-color: #374151; }
            .submissions-header, .detail-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .submissions-title, .detail-name, .section-title { color: #f9fafb; }
            .submission-item { border-color: #374151; }
            .submission-item:hover { background: #374151; }
            .submission-item.active { background: #4c1d95; }
            .submission-name, .file-name { color: #f9fafb; }
        }
    </style>

    @php
        $assignment = $record;
        $submissions = $this->submissions;
        $selected = $this->selectedSubmission;
        $stats = $this->stats;
    @endphp

    <div class="assignment-view">
        {{-- Assignment Header --}}
        <div class="assignment-header">
            <div class="header-content">
                <div class="header-top">
                    <div class="header-info">
                        <div class="header-icon">{{ $assignment->type === 'project' ? '📁' : '📋' }}</div>
                        <div class="header-text">
                            <h1>{{ $assignment->title }}</h1>
                            <p>{{ $assignment->course?->title }} • {{ $assignment->lesson?->title }}</p>
                        </div>
                    </div>
                    
                    <div class="header-meta">
                        <div class="meta-item">
                            <span class="meta-label">الدرجة الكاملة:</span>
                            <span class="meta-value">{{ $assignment->max_score }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">درجة النجاح:</span>
                            <span class="meta-value">{{ $assignment->passing_score }}</span>
                        </div>
                        @if($assignment->due_date)
                        <div class="meta-item" style="{{ $assignment->isOverdue() ? 'background: rgba(239, 68, 68, 0.3);' : '' }}">
                            <span class="meta-label">موعد التسليم:</span>
                            <span class="meta-value">{{ $assignment->due_date->format('Y/m/d H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">إجمالي التسليمات</div>
                    </div>
                    <div class="stat-box" style="background: rgba(251, 191, 36, 0.3);">
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                        <div class="stat-label">بانتظار التقييم</div>
                    </div>
                    <div class="stat-box" style="background: rgba(34, 197, 94, 0.3);">
                        <div class="stat-value">{{ $stats['graded'] }}</div>
                        <div class="stat-label">تم تقييمها</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">{{ number_format($stats['average_score'], 1) }}</div>
                        <div class="stat-label">متوسط الدرجات</div>
                    </div>
                    <div class="stat-box" style="background: rgba(34, 197, 94, 0.3);">
                        <div class="stat-value">{{ $stats['passed'] }}</div>
                        <div class="stat-label">ناجحون</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Content Area --}}
        <div class="content-area">
            {{-- Submissions List --}}
            <div class="submissions-list">
                <div class="submissions-header">
                    <h3 class="submissions-title">
                        <span>📤</span>
                        التسليمات ({{ $submissions->count() }})
                    </h3>
                </div>
                <div class="submissions-body">
                    @forelse($submissions as $submission)
                        <div 
                            class="submission-item {{ $selectedSubmissionId === $submission->id ? 'active' : '' }}"
                            wire:click="selectSubmission({{ $submission->id }})"
                        >
                            <div class="submission-avatar">
                                {{ mb_substr($submission->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div class="submission-info">
                                <p class="submission-name">{{ $submission->user?->name }}</p>
                                <p class="submission-meta">
                                    {{ $submission->submitted_at->diffForHumans() }}
                                    @if($submission->is_late)
                                        <span class="late-badge">متأخر</span>
                                    @endif
                                </p>
                            </div>
                            <span class="submission-status {{ $submission->status }}">
                                {{ \App\Models\AssignmentSubmission::getStatuses()[$submission->status] ?? $submission->status }}
                            </span>
                        </div>
                    @empty
                        <div class="empty-detail">
                            <div class="empty-icon">📭</div>
                            <p class="empty-title">لا توجد تسليمات</p>
                            <p class="empty-text">لم يقم أي طالب بتسليم هذا الواجب بعد</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Detail Panel --}}
            <div class="detail-panel">
                @if($selected)
                    <div class="detail-header">
                        <div class="detail-user">
                            <div class="detail-avatar">
                                {{ mb_substr($selected->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <p class="detail-name">{{ $selected->user?->name }}</p>
                                <p class="detail-email">{{ $selected->user?->email }}</p>
                            </div>
                        </div>
                        @if($selected->score !== null)
                        <div class="detail-score">
                            {{ $selected->score }}<span>/{{ $assignment->max_score }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="detail-body">
                        {{-- Files --}}
                        @if($selected->files->count() > 0)
                        <h4 class="section-title">
                            <span>📎</span>
                            الملفات المرفقة
                        </h4>
                        <div class="files-list">
                            @foreach($selected->files as $file)
                                <div class="file-item">
                                    <div class="file-icon">📄</div>
                                    <div class="file-info">
                                        <div class="file-name">{{ $file->file_name }}</div>
                                        <div class="file-size">{{ $file->getFormattedSize() }}</div>
                                    </div>
                                    <a href="{{ $file->getUrl() }}" target="_blank" class="file-download">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        تحميل
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endif
                        
                        {{-- Content --}}
                        @if($selected->content)
                        <h4 class="section-title">
                            <span>📝</span>
                            محتوى التسليم
                        </h4>
                        <div class="content-box">
                            {!! nl2br(e($selected->content)) !!}
                        </div>
                        @endif
                        
                        {{-- Grading Section --}}
                        <div class="grading-section">
                            <h4 class="grading-title">
                                <span>⭐</span>
                                تقييم الواجب
                            </h4>
                            
                            <div class="grading-row">
                                <div class="grading-input-group">
                                    <div class="grading-label">الدرجة (من {{ $assignment->max_score }})</div>
                                    <input 
                                        type="number" 
                                        class="grading-input" 
                                        wire:model="gradeScore"
                                        min="0" 
                                        max="{{ $assignment->max_score }}"
                                        placeholder="أدخل الدرجة"
                                    >
                                </div>
                            </div>
                            
                            <div class="grading-input-group" style="margin-bottom: 16px;">
                                <div class="grading-label">ملاحظات للطالب</div>
                                <textarea 
                                    class="grading-textarea" 
                                    wire:model="gradeFeedback"
                                    placeholder="أضف ملاحظاتك وتعليقاتك على الواجب..."
                                ></textarea>
                            </div>
                            
                            <div class="grading-actions">
                                <button class="grade-btn primary" wire:click="gradeSubmission">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    حفظ التقييم
                                </button>
                                <button class="grade-btn secondary" wire:click="requestResubmission">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    طلب إعادة التسليم
                                </button>
                            </div>
                        </div>
                        
                        {{-- Comments Section --}}
                        <div class="comments-section">
                            <h4 class="section-title">
                                <span>💬</span>
                                التعليقات ({{ $selected->comments->count() }})
                            </h4>
                            
                            @if($selected->comments->count() > 0)
                            <div class="comments-list">
                                @foreach($selected->comments as $comment)
                                    <div class="comment-item">
                                        <div class="comment-avatar">
                                            {{ mb_substr($comment->user?->name ?? '?', 0, 1) }}
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-name">{{ $comment->user?->name }}</span>
                                                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="comment-text">{{ $comment->content }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            
                            <div class="add-comment">
                                <input 
                                    type="text" 
                                    wire:model="newComment"
                                    wire:keydown.enter="addComment"
                                    placeholder="أضف تعليقاً..."
                                >
                                <button wire:click="addComment">إرسال</button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-detail">
                        <div class="empty-icon">👈</div>
                        <p class="empty-title">اختر تسليماً للعرض</p>
                        <p class="empty-text">اختر تسليماً من القائمة لعرض تفاصيله وتقييمه</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

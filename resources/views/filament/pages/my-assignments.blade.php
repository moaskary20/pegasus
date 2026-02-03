<x-filament-panels::page>
    <style>
        .my-assignments { max-width: 100%; }
        
        .assignments-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(6, 182, 212, 0.3);
            position: relative;
            overflow: hidden;
        }
        .assignments-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .header-info { display: flex; align-items: center; gap: 16px; }
        .header-icon {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .header-text h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .header-text p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        
        .header-stats { display: flex; gap: 16px; flex-wrap: wrap; }
        .header-stat {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 14px 20px;
            text-align: center;
            min-width: 90px;
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
            transition: all 0.3s;
        }
        .header-stat:hover { background: rgba(255,255,255,0.3); }
        .header-stat.active { background: white; color: #0891b2; }
        .header-stat-value { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
        .header-stat-label { font-size: 11px; opacity: 0.9; margin-top: 4px; }
        .header-stat.active .header-stat-label { opacity: 1; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        .card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .card-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .card-body { padding: 0; max-height: 500px; overflow-y: auto; }
        
        .assignment-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s;
        }
        .assignment-item:hover { background: #f9fafb; }
        .assignment-item.active { background: #ecfeff; border-right: 3px solid #06b6d4; }
        .assignment-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .assignment-icon.assignment { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .assignment-icon.project { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .assignment-info { flex: 1; min-width: 0; }
        .assignment-title { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .assignment-course { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        .assignment-due { font-size: 11px; color: #9ca3af; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .assignment-due.overdue { color: #dc2626; }
        .assignment-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .assignment-badge.pending { background: #fef3c7; color: #92400e; }
        .assignment-badge.submitted { background: #dbeafe; color: #1e40af; }
        .assignment-badge.graded { background: #dcfce7; color: #166534; }
        
        .detail-panel { display: flex; flex-direction: column; }
        .detail-header {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 24px;
        }
        .detail-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 12px;
        }
        .detail-title { font-size: 20px; font-weight: 700; margin: 0 0 8px 0; }
        .detail-course { font-size: 14px; opacity: 0.9; }
        .detail-meta { display: flex; gap: 20px; margin-top: 16px; flex-wrap: wrap; }
        .detail-meta-item {
            background: rgba(255,255,255,0.15);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
        }
        
        .detail-body { padding: 24px; flex: 1; overflow-y: auto; }
        
        .section { margin-bottom: 24px; }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-content {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }
        
        .submission-form {
            background: linear-gradient(135deg, #ecfeff, #cffafe);
            border: 1px solid #a5f3fc;
            border-radius: 14px;
            padding: 20px;
        }
        .form-title { font-size: 15px; font-weight: 700; color: #0e7490; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; }
        .form-group { margin-bottom: 16px; }
        .form-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 6px; display: block; }
        .form-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px 16px;
            border: 2px solid #a5f3fc;
            border-radius: 10px;
            font-size: 14px;
            resize: vertical;
        }
        .form-textarea:focus { outline: none; border-color: #06b6d4; }
        .form-file-upload {
            border: 2px dashed #a5f3fc;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .form-file-upload:hover { border-color: #06b6d4; background: rgba(6, 182, 212, 0.05); }
        .form-file-icon { font-size: 32px; margin-bottom: 8px; }
        .form-file-text { font-size: 13px; color: #6b7280; }
        .form-file-list { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
        .form-file-item {
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        
        .previous-submission {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .prev-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .prev-title { font-size: 14px; font-weight: 700; color: #166534; margin: 0; }
        .prev-score { font-size: 24px; font-weight: 800; color: #16a34a; }
        .prev-score span { font-size: 14px; color: #6b7280; }
        .prev-feedback {
            background: white;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            color: #374151;
            margin-top: 12px;
        }
        
        .empty-state {
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
        
        @media (prefers-color-scheme: dark) {
            .card { background: #1f2937; border-color: #374151; }
            .card-header { background: linear-gradient(135deg, #1f2937, #374151); border-color: #374151; }
            .card-title, .assignment-title, .section-title { color: #f9fafb; }
            .assignment-item { border-color: #374151; }
            .assignment-item:hover { background: #374151; }
            .assignment-item.active { background: #164e63; }
        }
    </style>

    @php
        $assignments = $this->filteredAssignments;
        $selected = $this->selectedAssignment;
        $stats = $this->stats;
    @endphp

    <div class="my-assignments">
        {{-- Header --}}
        <div class="assignments-header">
            <div class="header-content">
                <div class="header-info">
                    <div class="header-icon">📝</div>
                    <div class="header-text">
                        <h1>واجباتي</h1>
                        <p>تتبع وتسليم الواجبات والمشاريع</p>
                    </div>
                </div>
                
                <div class="header-stats">
                    <div class="header-stat {{ $activeTab === 'all' ? 'active' : '' }}" wire:click="setTab('all')">
                        <p class="header-stat-value">{{ $stats['total'] }}</p>
                        <p class="header-stat-label">الكل</p>
                    </div>
                    <div class="header-stat {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
                        <p class="header-stat-value">{{ $stats['pending'] }}</p>
                        <p class="header-stat-label">غير مسلم</p>
                    </div>
                    <div class="header-stat {{ $activeTab === 'submitted' ? 'active' : '' }}" wire:click="setTab('submitted')">
                        <p class="header-stat-value">{{ $stats['submitted'] }}</p>
                        <p class="header-stat-label">مسلم</p>
                    </div>
                    <div class="header-stat {{ $activeTab === 'graded' ? 'active' : '' }}" wire:click="setTab('graded')">
                        <p class="header-stat-value">{{ $stats['graded'] }}</p>
                        <p class="header-stat-label">مقيم</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-grid">
            {{-- Assignments List --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span>📋</span>
                        الواجبات
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($assignments as $assignment)
                        @php
                            $lastSubmission = $assignment->submissions->last();
                            $status = $lastSubmission ? $lastSubmission->status : 'pending';
                        @endphp
                        <div 
                            class="assignment-item {{ $selectedAssignmentId === $assignment->id ? 'active' : '' }}"
                            wire:click="selectAssignment({{ $assignment->id }})"
                        >
                            <div class="assignment-icon {{ $assignment->type }}">
                                {{ $assignment->type === 'project' ? '📁' : '📄' }}
                            </div>
                            <div class="assignment-info">
                                <p class="assignment-title">{{ $assignment->title }}</p>
                                <p class="assignment-course">{{ $assignment->course?->title }}</p>
                                @if($assignment->due_date)
                                    <p class="assignment-due {{ $assignment->isOverdue() ? 'overdue' : '' }}">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $assignment->due_date->format('Y/m/d H:i') }}
                                        @if($assignment->isOverdue())
                                            (منتهي)
                                        @else
                                            ({{ $assignment->due_date->diffForHumans() }})
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <span class="assignment-badge {{ $status }}">
                                @switch($status)
                                    @case('submitted') بانتظار التقييم @break
                                    @case('graded') تم التقييم @break
                                    @case('resubmit_requested') أعد التسليم @break
                                    @default غير مسلم
                                @endswitch
                            </span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p class="empty-title">لا توجد واجبات</p>
                            <p class="empty-text">لا توجد واجبات في هذا التصنيف</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Detail Panel --}}
            <div class="card detail-panel">
                @if($selected)
                    <div class="detail-header">
                        <div class="detail-type">
                            {{ $selected->type === 'project' ? '📁 مشروع' : '📄 واجب' }}
                        </div>
                        <h2 class="detail-title">{{ $selected->title }}</h2>
                        <p class="detail-course">{{ $selected->course?->title }} • {{ $selected->lesson?->title }}</p>
                        
                        <div class="detail-meta">
                            <div class="detail-meta-item">
                                <strong>الدرجة:</strong> {{ $selected->max_score }}
                            </div>
                            <div class="detail-meta-item">
                                <strong>درجة النجاح:</strong> {{ $selected->passing_score }}
                            </div>
                            @if($selected->due_date)
                            <div class="detail-meta-item" style="{{ $selected->isOverdue() ? 'background: rgba(239,68,68,0.3);' : '' }}">
                                <strong>الموعد:</strong> {{ $selected->due_date->format('Y/m/d H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="detail-body">
                        {{-- Description --}}
                        <div class="section">
                            <h4 class="section-title">
                                <span>📋</span>
                                وصف الواجب
                            </h4>
                            <div class="section-content">
                                {!! $selected->description !!}
                            </div>
                        </div>
                        
                        @if($selected->instructions)
                        <div class="section">
                            <h4 class="section-title">
                                <span>📝</span>
                                تعليمات التسليم
                            </h4>
                            <div class="section-content">
                                {!! $selected->instructions !!}
                            </div>
                        </div>
                        @endif
                        
                        @php $lastSubmission = $selected->submissions->last(); @endphp
                        
                        {{-- Previous Submission --}}
                        @if($lastSubmission && $lastSubmission->status === 'graded')
                            <div class="previous-submission">
                                <div class="prev-header">
                                    <p class="prev-title">✅ تم التقييم</p>
                                    <div class="prev-score">
                                        {{ $lastSubmission->score }}<span>/{{ $selected->max_score }}</span>
                                    </div>
                                </div>
                                @if($lastSubmission->feedback)
                                    <div class="prev-feedback">
                                        <strong>ملاحظات المدرس:</strong><br>
                                        {{ $lastSubmission->feedback }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        {{-- Submission Form --}}
                        @if($selected->canSubmit(auth()->user()))
                            <div class="submission-form">
                                <h4 class="form-title">
                                    <span>📤</span>
                                    {{ $lastSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}
                                </h4>
                                
                                <div class="form-group">
                                    <label class="form-label">محتوى الإجابة (اختياري)</label>
                                    <textarea 
                                        class="form-textarea" 
                                        wire:model="submissionContent"
                                        placeholder="اكتب إجابتك أو ملاحظاتك هنا..."
                                    ></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">الملفات المرفقة</label>
                                    <div class="form-file-upload">
                                        <input type="file" wire:model="submissionFiles" multiple class="hidden" id="file-upload">
                                        <label for="file-upload" style="cursor: pointer; display: block;">
                                            <div class="form-file-icon">📎</div>
                                            <div class="form-file-text">اضغط لاختيار الملفات أو اسحبها هنا</div>
                                            @if($selected->allowed_file_types)
                                                <div class="form-file-text" style="margin-top: 4px; font-size: 11px;">
                                                    الأنواع المسموحة: {{ implode(', ', $selected->allowed_file_types) }}
                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                    
                                    @if(count($submissionFiles) > 0)
                                        <div class="form-file-list">
                                            @foreach($submissionFiles as $file)
                                                <div class="form-file-item">
                                                    📄 {{ $file->getClientOriginalName() }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                
                                <button 
                                    class="submit-btn" 
                                    wire:click="submitAssignment"
                                    wire:loading.attr="disabled"
                                >
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    <span wire:loading.remove>تسليم الواجب</span>
                                    <span wire:loading>جاري التسليم...</span>
                                </button>
                            </div>
                        @elseif($lastSubmission && $lastSubmission->status === 'submitted')
                            <div class="previous-submission" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd;">
                                <p style="text-align: center; color: #1e40af; font-weight: 600;">
                                    ⏳ تم تسليم الواجب وبانتظار التقييم
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">👈</div>
                        <p class="empty-title">اختر واجباً للعرض</p>
                        <p class="empty-text">اختر واجباً من القائمة لعرض تفاصيله وتسليمه</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

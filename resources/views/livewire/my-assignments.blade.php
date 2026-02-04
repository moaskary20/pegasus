<div>
    @php
        $assignments = $this->filteredAssignments;
        $selected = $this->selectedAssignment;
        $stats = $this->stats;
    @endphp

    <style>
        .my-assignments-site { max-width: 100%; }
        .assignments-header-site {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
            border-radius: 20px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(6, 182, 212, 0.3);
            position: relative;
            overflow: hidden;
        }
        .assignments-header-site::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }
        .header-content-site { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .header-info-site { display: flex; align-items: center; gap: 16px; }
        .header-icon-site {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        .header-text-site h1 { font-size: 26px; font-weight: 800; margin: 0; }
        .header-text-site p { font-size: 14px; opacity: 0.9; margin: 6px 0 0 0; }
        .header-stats-site { display: flex; gap: 16px; flex-wrap: wrap; }
        .header-stat-site {
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
        .header-stat-site:hover { background: rgba(255,255,255,0.3); }
        .header-stat-site.active { background: white; color: #0891b2; }
        .header-stat-value-site { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
        .header-stat-label-site { font-size: 11px; opacity: 0.9; margin-top: 4px; }
        .header-stat-site.active .header-stat-label-site { opacity: 1; }
        .content-grid-site {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .content-grid-site { grid-template-columns: 1fr; }
        }
        .card-site {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .card-header-site {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .card-title-site { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; display: flex; align-items: center; gap: 8px; }
        .card-body-site { padding: 0; max-height: 500px; overflow-y: auto; }
        .assignment-item-site {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: all 0.2s;
        }
        .assignment-item-site:hover { background: #f9fafb; }
        .assignment-item-site.active { background: #ecfeff; border-right: 3px solid #06b6d4; }
        .assignment-icon-site {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .assignment-icon-site.assignment { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
        .assignment-icon-site.project { background: linear-gradient(135deg, #fef3c7, #fde68a); }
        .assignment-info-site { flex: 1; min-width: 0; }
        .assignment-title-site { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; }
        .assignment-course-site { font-size: 12px; color: #6b7280; margin: 4px 0 0 0; }
        .assignment-due-site { font-size: 11px; color: #9ca3af; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .assignment-due-site.overdue { color: #dc2626; }
        .assignment-badge-site {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .assignment-badge-site.pending { background: #fef3c7; color: #92400e; }
        .assignment-badge-site.submitted { background: #dbeafe; color: #1e40af; }
        .assignment-badge-site.graded { background: #dcfce7; color: #166534; }
        .detail-panel-site { display: flex; flex-direction: column; }
        .detail-header-site {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 24px;
        }
        .detail-type-site {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 12px;
        }
        .detail-title-site { font-size: 20px; font-weight: 700; margin: 0 0 8px 0; }
        .detail-course-site { font-size: 14px; opacity: 0.9; }
        .detail-meta-site { display: flex; gap: 20px; margin-top: 16px; flex-wrap: wrap; }
        .detail-meta-item-site {
            background: rgba(255,255,255,0.15);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
        }
        .detail-body-site { padding: 24px; flex: 1; overflow-y: auto; }
        .section-site { margin-bottom: 24px; }
        .section-title-site {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-content-site {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }
        .submission-form-site {
            background: linear-gradient(135deg, #ecfeff, #cffafe);
            border: 1px solid #a5f3fc;
            border-radius: 14px;
            padding: 20px;
        }
        .form-title-site { font-size: 15px; font-weight: 700; color: #0e7490; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; }
        .form-group-site { margin-bottom: 16px; }
        .form-label-site { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 6px; display: block; }
        .form-textarea-site {
            width: 100%;
            min-height: 120px;
            padding: 12px 16px;
            border: 2px solid #a5f3fc;
            border-radius: 10px;
            font-size: 14px;
            resize: vertical;
        }
        .form-textarea-site:focus { outline: none; border-color: #06b6d4; }
        .form-file-upload-site {
            border: 2px dashed #a5f3fc;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .form-file-upload-site:hover { border-color: #06b6d4; background: rgba(6, 182, 212, 0.05); }
        .form-file-icon-site { font-size: 32px; margin-bottom: 8px; }
        .form-file-text-site { font-size: 13px; color: #6b7280; }
        .form-file-list-site { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
        .form-file-item-site {
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .submit-btn-site {
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
        .submit-btn-site:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4); }
        .submit-btn-site:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .previous-submission-site {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .prev-header-site { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .prev-title-site { font-size: 14px; font-weight: 700; color: #166534; margin: 0; }
        .prev-score-site { font-size: 24px; font-weight: 800; color: #16a34a; }
        .prev-score-site span { font-size: 14px; color: #6b7280; }
        .prev-feedback-site {
            background: white;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            color: #374151;
            margin-top: 12px;
        }
        .empty-state-site {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            text-align: center;
        }
        .empty-icon-site { font-size: 64px; margin-bottom: 16px; }
        .empty-title-site { font-size: 18px; font-weight: 600; color: #374151; margin: 0 0 8px 0; }
        .empty-text-site { font-size: 14px; color: #6b7280; }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8 md:py-12" style="direction: rtl;">
        <div class="my-assignments-site">
            {{-- Header --}}
            <div class="assignments-header-site">
                <div class="header-content-site">
                    <div class="header-info-site">
                        <div class="header-icon-site">📝</div>
                        <div class="header-text-site">
                            <h1>واجباتي</h1>
                            <p>تتبع وتسليم الواجبات والمشاريع</p>
                        </div>
                    </div>

                    <div class="header-stats-site">
                        <div class="header-stat-site {{ $activeTab === 'all' ? 'active' : '' }}" wire:click="setTab('all')">
                            <p class="header-stat-value-site">{{ $stats['total'] }}</p>
                            <p class="header-stat-label-site">الكل</p>
                        </div>
                        <div class="header-stat-site {{ $activeTab === 'pending' ? 'active' : '' }}" wire:click="setTab('pending')">
                            <p class="header-stat-value-site">{{ $stats['pending'] }}</p>
                            <p class="header-stat-label-site">غير مسلم</p>
                        </div>
                        <div class="header-stat-site {{ $activeTab === 'submitted' ? 'active' : '' }}" wire:click="setTab('submitted')">
                            <p class="header-stat-value-site">{{ $stats['submitted'] }}</p>
                            <p class="header-stat-label-site">مسلم</p>
                        </div>
                        <div class="header-stat-site {{ $activeTab === 'graded' ? 'active' : '' }}" wire:click="setTab('graded')">
                            <p class="header-stat-value-site">{{ $stats['graded'] }}</p>
                            <p class="header-stat-label-site">مقيم</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-grid-site">
                {{-- Assignments List --}}
                <div class="card-site">
                    <div class="card-header-site">
                        <h3 class="card-title-site">
                            <span>📋</span>
                            الواجبات
                        </h3>
                    </div>
                    <div class="card-body-site">
                        @forelse($assignments as $assignment)
                            @php
                                $lastSubmission = $assignment->submissions->last();
                                $status = $lastSubmission ? $lastSubmission->status : 'pending';
                            @endphp
                            <div
                                class="assignment-item-site {{ $selectedAssignmentId === $assignment->id ? 'active' : '' }}"
                                wire:click="selectAssignment({{ $assignment->id }})"
                            >
                                <div class="assignment-icon-site {{ $assignment->type }}">
                                    {{ $assignment->type === 'project' ? '📁' : '📄' }}
                                </div>
                                <div class="assignment-info-site">
                                    <p class="assignment-title-site">{{ $assignment->title }}</p>
                                    <p class="assignment-course-site">{{ $assignment->course?->title }}</p>
                                    @if($assignment->due_date)
                                        <p class="assignment-due-site {{ $assignment->isOverdue() ? 'overdue' : '' }}">
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
                                <span class="assignment-badge-site {{ $status }}">
                                    @switch($status)
                                        @case('submitted') بانتظار التقييم @break
                                        @case('graded') تم التقييم @break
                                        @case('resubmit_requested') أعد التسليم @break
                                        @default غير مسلم
                                    @endswitch
                                </span>
                            </div>
                        @empty
                            <div class="empty-state-site">
                                <div class="empty-icon-site">📭</div>
                                <p class="empty-title-site">لا توجد واجبات</p>
                                <p class="empty-text-site">لا توجد واجبات في هذا التصنيف</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Detail Panel --}}
                <div class="card-site detail-panel-site">
                    @if($selected)
                        <div class="detail-header-site">
                            <div class="detail-type-site">
                                {{ $selected->type === 'project' ? '📁 مشروع' : '📄 واجب' }}
                            </div>
                            <h2 class="detail-title-site">{{ $selected->title }}</h2>
                            <p class="detail-course-site">{{ $selected->course?->title }} • {{ $selected->lesson?->title }}</p>

                            <div class="detail-meta-site">
                                <div class="detail-meta-item-site">
                                    <strong>الدرجة:</strong> {{ $selected->max_score }}
                                </div>
                                <div class="detail-meta-item-site">
                                    <strong>درجة النجاح:</strong> {{ $selected->passing_score }}
                                </div>
                                @if($selected->due_date)
                                    <div class="detail-meta-item-site" style="{{ $selected->isOverdue() ? 'background: rgba(239,68,68,0.3);' : '' }}">
                                        <strong>الموعد:</strong> {{ $selected->due_date->format('Y/m/d H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="detail-body-site">
                            {{-- Description --}}
                            <div class="section-site">
                                <h4 class="section-title-site">
                                    <span>📋</span>
                                    وصف الواجب
                                </h4>
                                <div class="section-content-site">
                                    {!! $selected->description !!}
                                </div>
                            </div>

                            @if($selected->instructions)
                                <div class="section-site">
                                    <h4 class="section-title-site">
                                        <span>📝</span>
                                        تعليمات التسليم
                                    </h4>
                                    <div class="section-content-site">
                                        {!! $selected->instructions !!}
                                    </div>
                                </div>
                            @endif

                            @php $lastSubmission = $selected->submissions->last(); @endphp

                            {{-- Previous Submission --}}
                            @if($lastSubmission && $lastSubmission->status === 'graded')
                                <div class="previous-submission-site">
                                    <div class="prev-header-site">
                                        <p class="prev-title-site">✅ تم التقييم</p>
                                        <div class="prev-score-site">
                                            {{ $lastSubmission->score }}<span>/{{ $selected->max_score }}</span>
                                        </div>
                                    </div>
                                    @if($lastSubmission->feedback)
                                        <div class="prev-feedback-site">
                                            <strong>ملاحظات المدرس:</strong><br>
                                            {{ $lastSubmission->feedback }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Submission Form --}}
                            @if($selected->canSubmit(auth()->user()))
                                <div class="submission-form-site">
                                    <h4 class="form-title-site">
                                        <span>📤</span>
                                        {{ $lastSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}
                                    </h4>

                                    <div class="form-group-site">
                                        <label class="form-label-site">محتوى الإجابة (اختياري)</label>
                                        <textarea
                                            class="form-textarea-site"
                                            wire:model="submissionContent"
                                            placeholder="اكتب إجابتك أو ملاحظاتك هنا..."
                                        ></textarea>
                                    </div>

                                    <div class="form-group-site">
                                        <label class="form-label-site">الملفات المرفقة</label>
                                        <div class="form-file-upload-site">
                                            <input type="file" wire:model="submissionFiles" multiple class="hidden" id="file-upload-site">
                                            <label for="file-upload-site" style="cursor: pointer; display: block;">
                                                <div class="form-file-icon-site">📎</div>
                                                <div class="form-file-text-site">اضغط لاختيار الملفات أو اسحبها هنا</div>
                                                @if($selected->allowed_file_types)
                                                    <div class="form-file-text-site" style="margin-top: 4px; font-size: 11px;">
                                                        الأنواع المسموحة: {{ implode(', ', $selected->allowed_file_types) }}
                                                    </div>
                                                @endif
                                            </label>
                                        </div>

                                        @if(count($submissionFiles) > 0)
                                            <div class="form-file-list-site">
                                                @foreach($submissionFiles as $file)
                                                    <div class="form-file-item-site">
                                                        📄 {{ $file->getClientOriginalName() }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <button
                                        class="submit-btn-site"
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
                                <div class="previous-submission-site" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd;">
                                    <p style="text-align: center; color: #1e40af; font-weight: 600;">
                                        ⏳ تم تسليم الواجب وبانتظار التقييم
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state-site">
                            <div class="empty-icon-site">👈</div>
                            <p class="empty-title-site">اختر واجباً للعرض</p>
                            <p class="empty-text-site">اختر واجباً من القائمة لعرض تفاصيله وتسليمه</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        Livewire.on('notify', (e) => {
            const type = e.type || 'info';
            const message = e.message || '';
            if (message && typeof window.showNotice === 'function') {
                window.showNotice(type, message);
            } else {
                alert(message);
            }
        });
    </script>
    @endscript
</div>

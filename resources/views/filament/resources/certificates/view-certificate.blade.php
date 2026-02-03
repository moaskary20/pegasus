<x-filament-panels::page>
    @if($this->record->pdf_path)
        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">الشهادة</h3>
                    <div class="flex gap-2">
                        <a href="{{ $this->record->getPdfUrl() }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            تحميل PDF
                        </a>
                        <a href="{{ $this->record->getPdfUrl() }}" 
                           onclick="window.open(this.href); window.print(); return false;"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            طباعة
                        </a>
                    </div>
                </div>
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>ملاحظة:</strong> بعد تعديل النصوص، يجب إعادة إنشاء ملف PDF باستخدام زر "إنشاء PDF" في الأعلى.
                    </p>
                </div>
                <iframe src="{{ $this->record->getPdfUrl() }}" 
                        class="w-full h-[800px] border rounded-lg"
                        frameborder="0"
                        id="certificate-iframe">
                </iframe>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">معلومات الشهادة</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">الطالب</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $this->record->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">الدورة</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $this->record->course->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">تاريخ الإصدار</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $this->record->issued_at->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">رقم الشهادة</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $this->record->uuid }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-yellow-800">ملف PDF غير متوفر</h3>
            <p class="mt-2 text-sm text-yellow-700">لم يتم إنشاء ملف PDF للشهادة بعد. يمكنك إنشاؤه باستخدام الزر أعلاه.</p>
        </div>
    @endif
</x-filament-panels::page>

# 🔧 الملفات المصدرية - دليل المطور

---

## 📁 الملفات الجديدة

### 1. `app/Models/ZoomMeeting.php` ✅

**الغرض:** موديل قاعدة البيانات لاجتماعات Zoom

**الموقع:**
```
app/
└── Models/
    └── ZoomMeeting.php (نموديل جديد)
```

**الخصائص:**
```php
protected $fillable = [
    'lesson_id',
    'zoom_meeting_id',
    'topic',
    'description',
    'scheduled_start_time',
    'duration',
    'timezone',
    'join_url',
    'start_url',
    'password',
    'host_id',
    'status',
];
```

**العلاقات:**
```php
public function lesson(): BelongsTo
// علاقة Belongs-To مع Lesson
// Many-to-One: عدة اجتماعات ممكنة لدرس واحد؟ لا
// One-to-One: درس واحد له اجتماع واحد
```

**الاستخدام:**
```php
$meeting = Lesson::find(1)->zoomMeeting;
echo $meeting->join_url; // رابط الانضمام
echo $meeting->status;   // حالة الاجتماع
```

---

### 2. `app/Services/ZoomAPIService.php` ✅

**الغرض:** خدمة للتكامل مع Zoom API

**الموقع:**
```
app/
└── Services/
    └── ZoomAPIService.php (خدمة جديدة)
```

**الوظائف الرئيسية:**

#### `createMeeting()`
```php
public function createMeeting(
    Lesson $lesson,
    string $scheduledTime,
    int $duration = 60
): ?ZoomMeeting

// الاستخدام:
$zoomService = new ZoomAPIService();
$meeting = $zoomService->createMeeting(
    $lesson,
    '2026-02-15 14:30:00',
    90
);
```

#### `updateMeeting()`
```php
public function updateMeeting(
    ZoomMeeting $zoomMeeting,
    array $data
): bool

// الاستخدام:
$zoomService->updateMeeting($meeting, [
    'topic' => 'عنوان جديد',
    'duration' => 120,
]);
```

#### `deleteMeeting()`
```php
public function deleteMeeting(
    ZoomMeeting $zoomMeeting
): bool

// الاستخدام:
$zoomService->deleteMeeting($meeting);
// يضبط status = 'cancelled'
```

#### `testConnection()`
```php
public function testConnection(): array

// النتيجة:
[
    'success' => true,
    'message' => 'تم الاتصال بنجاح',
    'user' => [...user_data...]
]
```

---

### 3. `app/Filament/Resources/Sections/Actions/CreateZoomMeetingAction.php` ✅

**الغرض:** Action لإنشاء اجتماع من لوحة التحكم

**الموقع:**
```
app/
└── Filament/
    └── Resources/
        └── Sections/
            └── Actions/
                └── CreateZoomMeetingAction.php (Action جديد)
```

**الاستخدام:**
```php
// يُستخدم في RelationManager
$record->dispatch(CreateZoomMeetingAction::make());

// يعرض نموذج بـ 3 حقول:
// 1. موعد الاجتماع
// 2. المدة
// 3. كلمة المرور (اختياري)
```

---

### 4. `database/migrations/2026_01_29_create_zoom_meetings_table.php` ✅

**الغرض:** Migration لإنشاء جدول zoom_meetings

**الموقع:**
```
database/
└── migrations/
    └── 2026_01_29_create_zoom_meetings_table.php (Migration جديد)
```

**الأعمدة:**
```sql
CREATE TABLE zoom_meetings (
    id                      BIGINT PRIMARY KEY,
    lesson_id               BIGINT (FOREIGN KEY),
    zoom_meeting_id         VARCHAR (UNIQUE),
    topic                   VARCHAR,
    description             TEXT,
    scheduled_start_time    DATETIME,
    duration                INT,
    timezone                VARCHAR,
    join_url                LONGTEXT,
    start_url               LONGTEXT,
    password                VARCHAR,
    host_id                 VARCHAR,
    status                  ENUM,
    created_at              DATETIME,
    updated_at              DATETIME,
    deleted_at              DATETIME
);

// الفهارس:
- INDEX: lesson_id
- INDEX: status
- INDEX: scheduled_start_time
```

**الحالات المتاحة:**
```sql
ENUM VALUES:
'pending'    -- لم يتم الإنشاء
'scheduled'  -- مجدول
'started'    -- قيد الانعقاد
'ended'      -- انتهى
'cancelled'  -- ملغى
```

---

## 📝 الملفات المعدلة

### 1. `app/Models/Lesson.php` ✅

**التعديلات:**

**أضيف:**
```php
public function zoomMeeting(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(ZoomMeeting::class);
}
```

**الاستخدام:**
```php
$lesson = Lesson::find(1);
if ($lesson->zoomMeeting) {
    echo $lesson->zoomMeeting->join_url;
}
```

---

### 2. `app/Filament/Resources/Sections/RelationManagers/LessonsRelationManager.php` ✅

**التعديلات:**

**Import:**
```php
use Filament\Forms\Components\DateTimePicker;
```

**حقول جديدة في النموذج:**
```php
Toggle::make('has_zoom_meeting')
    ->label('إضافة اجتماع Zoom')
    ->default(false)
    ->reactive(),

DateTimePicker::make('zoom_scheduled_time')
    ->label('موعد الاجتماع')
    ->visible(fn ($get) => $get('has_zoom_meeting')),

TextInput::make('zoom_duration')
    ->label('مدة الاجتماع')
    ->numeric()
    ->default(60),

TextInput::make('zoom_password')
    ->label('كلمة مرور الاجتماع'),
```

**عمود جديد في الجدول:**
```php
TextColumn::make('zoom_meeting')
    ->label('اجتماع Zoom')
    ->getStateUsing(fn ($record) => 
        $record->zoomMeeting ? '📹 ' . $record->zoomMeeting->status : 'لا'
    )
    ->badge()
    ->color(fn ($record) => 
        $record->zoomMeeting ? match($record->zoomMeeting->status) {
            'scheduled' => 'info',
            'started' => 'success',
            // ...
        } : 'gray'
    ),
```

---

## 🔄 تدفق البيانات

### عند إنشاء اجتماع Zoom:

```
1. المدرس يفعّل Toggle: "إضافة اجتماع Zoom"
   ↓
2. تظهر الحقول:
   - موعد الاجتماع
   - المدة
   - كلمة المرور
   ↓
3. المدرس يملأ البيانات ويضغط حفظ
   ↓
4. Livewire يرسل البيانات إلى RelationManager
   ↓
5. RelationManager يستدعي ZoomAPIService
   ↓
6. ZoomAPIService ينشئ الاجتماع عبر Zoom API
   ↓
7. Zoom ترجع معرّف الاجتماع ورابط الانضمام
   ↓
8. ZoomAPIService يحفظ البيانات في ZoomMeeting
   ↓
9. RelationManager يعرض رسالة نجاح
   ↓
10. الطالب يرى الاجتماع عند فتح الدرس
```

---

## 🛠️ التعديلات الممكنة

### إضافة ميزة: نسخ الاجتماع

```php
// في ZoomAPIService
public function duplicateMeeting(
    ZoomMeeting $source,
    Lesson $newLesson
): ?ZoomMeeting {
    return $this->createMeeting(
        $newLesson,
        $source->scheduled_start_time->addWeek(),
        $source->duration
    );
}
```

### إضافة ميزة: الاجتماعات المتكررة

```php
// في ZoomAPIService
public function createRecurringMeeting(
    Lesson $lesson,
    string $startTime,
    string $recurrence // 'weekly', 'daily', etc
): Collection {
    // إنشاء مجموعة من الاجتماعات
}
```

### إضافة ميزة: التنبيهات

```php
// في ZoomMeeting (Model)
public function notifyParticipants(): void {
    // إرسال بريد تذكيري للطلاب
}
```

---

## 📊 قاعدة البيانات - الاستعلامات

### البحث عن اجتماعات مجدولة

```php
$scheduledMeetings = ZoomMeeting::where('status', 'scheduled')
    ->where('scheduled_start_time', '>=', now())
    ->orderBy('scheduled_start_time')
    ->get();
```

### البحث عن اجتماعات درس معين

```php
$meeting = ZoomMeeting::where('lesson_id', 1)
    ->first();

// أو عبر Relationship
$meeting = Lesson::find(1)->zoomMeeting;
```

### عد الاجتماعات حسب الحالة

```php
$stats = ZoomMeeting::groupBy('status')
    ->selectRaw('status, count(*) as total')
    ->get();
```

---

## 🔐 الأمان

### معالجة الأخطاء:

```php
try {
    $meeting = $zoomService->createMeeting($lesson, $time, $duration);
    if (!$meeting) {
        throw new Exception('فشل الإنشاء');
    }
} catch (Exception $e) {
    Log::error('Zoom Error: ' . $e->getMessage());
    return null;
}
```

### التحقق من البيانات:

```php
if (!$zoomService->isConfigured()) {
    // عدم إنشاء الاجتماع
    return false;
}
```

---

## 📝 السجلات (Logging)

### السجلات المُنتجة:

```php
// إنشاء ناجح
Log::info('Zoom Meeting Created', [
    'lesson_id' => $lesson->id,
    'zoom_meeting_id' => $response['id'],
]);

// خطأ في الإنشاء
Log::error('Zoom API: Failed to create meeting', [
    'response' => $response
]);
```

---

## 🧪 الاختبار

### اختبار إنشاء اجتماع:

```php
public function test_can_create_zoom_meeting()
{
    $lesson = Lesson::factory()->create();
    $service = new ZoomAPIService();
    
    $meeting = $service->createMeeting(
        $lesson,
        now()->addDay()->format('Y-m-d H:i:s'),
        60
    );
    
    $this->assertNotNull($meeting);
    $this->assertEquals('scheduled', $meeting->status);
}
```

### اختبار العلاقات:

```php
public function test_lesson_has_zoom_meeting()
{
    $lesson = Lesson::factory()->create();
    $meeting = ZoomMeeting::factory()
        ->for($lesson)
        ->create();
    
    $this->assertTrue($lesson->zoomMeeting->is($meeting));
}
```

---

## 📚 المراجع

| العنصر | الملف |
|--------|------|
| Model | `app/Models/ZoomMeeting.php` |
| Service | `app/Services/ZoomAPIService.php` |
| Action | `app/Filament/Resources/Sections/Actions/CreateZoomMeetingAction.php` |
| Migration | `database/migrations/2026_01_29_...` |
| RelationManager | `app/Filament/Resources/Sections/RelationManagers/LessonsRelationManager.php` |

---

## 🚀 البدء بالتطوير

### 1. فهم الملفات:
- اقرأ كل ملف وافهم وظيفته

### 2. تعديل الكود:
- أضف المزيد من الوظائف

### 3. اختبار:
- اكتب اختبارات للمزايا الجديدة

### 4. التوثيق:
- وثّق التغييرات

---

**تم الإعداد:** 29 يناير 2026  
**الإصدار:** 1.0  
**حالة الملفات:** ✅ جاهزة

# 🧪 اختبر الربط الآن!

**التاريخ:** 30 يناير 2026  
**الحالة:** ✅ **جاهز للاختبار**

---

## ✅ قائمة الفحص السريعة

### 1️⃣ التحقق من الملفات

```bash
# تأكد من وجود LessonObserver
test -f "app/Observers/LessonObserver.php" && echo "✅ موجود" || echo "❌ غير موجود"

# تأكد من الأخطاء
php -l app/Observers/LessonObserver.php
php -l app/Providers/AppServiceProvider.php
```

### 2️⃣ التحقق من الإعدادات

```bash
# ادخل Tinker
php artisan tinker

# تأكد من وجود إعدادات Zoom
>>> use App\Models\PlatformSetting;
>>> PlatformSetting::where('key', 'zoom_api_key')->exists()
# يجب: true ✅

>>> PlatformSetting::get('zoom_api_key')
# يجب: القيمة (ليس فارغ)
```

### 3️⃣ التحقق من البيانات

```bash
php artisan tinker

# تأكد من الأعمدة
>>> use Illuminate\Support\Facades\Schema;
>>> Schema::hasColumn('lessons', 'has_zoom_meeting')
# true ✅

>>> Schema::hasColumn('lessons', 'zoom_link')
# true ✅

>>> Schema::hasColumn('zoom_meetings', 'join_url')
# true ✅
```

### 4️⃣ التحقق من العلاقات

```bash
php artisan tinker

# تأكد من أن Lesson له علاقة ZoomMeeting
>>> $lesson = \App\Models\Lesson::first();
>>> $lesson->zoomMeeting()
# يجب: Illuminate\Database\Eloquent\Relations\HasOne ✅
```

---

## 🧪 اختبار عملي خطوة بخطوة

### المرحلة 1: التحضير

```bash
# 1. مسح Cache
php artisan optimize:clear

# 2. تحديث المتصفح
# CTRL+SHIFT+DEL → F5
```

### المرحلة 2: الإعدادات

```
ادخل الإدارة: /admin
اذهب: Settings → Platform Settings
تأكد:
  ✅ Zoom API Key مملوء
  ✅ Zoom API Secret مملوء
  ✅ Zoom User ID مملوء
  ✅ Zoom Account ID مملوء
احفظ
```

### المرحلة 3: إضافة درس

```
اذهب: Sections (الأقسام)
اختر: قسم
انقر: Add Lesson (أضيف درس)
ملء البيانات:
  📝 Title: "درس Zoom تجريبي"
  📋 Description: "اختبار الربط الأتوماتيكي"
  📂 Content Type: 📹 اضافه درس على زوم
  ☑ Has Zoom Meeting (فعّل)
  📅 Scheduled Time: 2026-02-01 14:00:00
  ⏱️ Duration: 60
  🔐 Password: test123
  🔗 Zoom Link: (اتركه فارغ - سيملأ تلقائياً!)
احفظ [💾 Save]
```

### المرحلة 4: التحقق من النجاح

#### ✅ في الإدارة:
```
1. افتح الدرس الذي أنشأته
2. شوف حقل "🔗 رابط اجتماع Zoom"
3. يجب أن تجد: https://zoom.us/j/...
   (ملأ تلقائياً!)
```

#### ✅ في Database:
```bash
php artisan tinker

# تحقق من الدرس
>>> $lesson = \App\Models\Lesson::latest()->first();
>>> $lesson->title
# "درس Zoom تجريبي"

>>> $lesson->zoom_link
# "https://zoom.us/j/123456789..."

>>> $lesson->zoomMeeting
# بيانات الاجتماع كاملة

>>> $lesson->zoomMeeting->status
# "scheduled"
```

#### ✅ في السجلات:
```bash
# شوف السجلات
tail -f storage/logs/laravel.log | grep -i zoom

# يجب أن ترى:
# [تم إنشاء اجتماع Zoom]
# lesson_id: X
# zoom_meeting_id: 123456789
```

---

## 📊 النتائج المتوقعة

### ✅ إذا كل شيء يعمل:

```
1. عند الحفظ:
   ✅ Observer يستدعى
   ✅ ZoomAPIService ينشئ الاجتماع
   ✅ الرابط يُحفظ تلقائياً
   ✅ رسالة نجاح في السجلات

2. في الإدارة:
   ✅ حقل zoom_link مملوء بالرابط
   ✅ في جدول zoom_meetings بيانات كاملة
   ✅ حالة الاجتماع: "scheduled"

3. في API:
   ✅ الاجتماع منشأ في Zoom
   ✅ رابط الانضمام يعمل
   ✅ المشاركون يستطيعون الدخول
```

### ❌ إذا لم يعمل:

#### المشكلة: لا يوجد رابط
```bash
# 1. تحقق من الإعدادات
php artisan tinker
>>> \App\Models\PlatformSetting::all()
# يجب أن تجد جميع مفاتيح Zoom

# 2. تحقق من السجلات
tail -f storage/logs/laravel.log | grep -i error

# 3. تحقق من API
# هل Zoom API يعمل؟
# هل البيانات صحيحة؟
```

#### المشكلة: خطأ في الحفظ
```bash
# تحقق من الأخطاء
php -l app/Observers/LessonObserver.php
php -l app/Providers/AppServiceProvider.php

# مسح Cache
php artisan optimize:clear

# جرب الحفظ مرة أخرى
```

#### المشكلة: لا يستدعي Observer
```bash
# تأكد من التسجيل
php artisan tinker
>>> \App\Models\Lesson::getObservers()
# يجب أن ترى: LessonObserver

# أعد القراءة:
>>> class_exists('App\Observers\LessonObserver')
# true ✅
```

---

## 🔍 اختبارات متقدمة

### اختبار 1: اختبر الحفظ المتكرر

```bash
php artisan tinker

# حفظ درس جديد
>>> $lesson = \App\Models\Lesson::create([
      'section_id' => 1,
      'title' => 'Test 1',
      'has_zoom_meeting' => true,
      'zoom_scheduled_time' => now()->addDay(),
      'zoom_duration' => 60,
    ]);
# يجب ينشئ الاجتماع تلقائياً!

>>> $lesson->zoom_link
# يجب يكون موجود
```

### اختبار 2: اختبر التحديث

```bash
php artisan tinker

>>> $lesson = \App\Models\Lesson::latest()->first();
>>> $lesson->update(['zoom_duration' => 120]);
# يجب ينشئ اجتماع جديد

>>> $lesson->zoom_link
# الرابط يجب يتحدث
```

### اختبار 3: اختبر تعطيل Zoom

```bash
php artisan tinker

>>> $lesson = \App\Models\Lesson::latest()->first();
>>> $lesson->update(['has_zoom_meeting' => false]);
# الرابط يجب يمسح

>>> $lesson->zoom_link
# null ✅
```

---

## 📋 نقاط التحقق

| النقطة | الحالة | الملاحظة |
|--------|--------|---------|
| LessonObserver موجود | ✅/❌ | الملف يجب يكون في app/Observers |
| Observer مسجل | ✅/❌ | في AppServiceProvider |
| الإعدادات موجودة | ✅/❌ | في Platform Settings |
| الأعمدة موجودة | ✅/❌ | في جدول lessons و zoom_meetings |
| العلاقات موجودة | ✅/❌ | Lesson -> ZoomMeeting |
| API يعمل | ✅/❌ | تحقق من credentials |
| Observer يستدعى | ✅/❌ | شوف السجلات |
| الرابط يملأ تلقائياً | ✅/❌ | في حقل zoom_link |

---

## 🎯 الخطوات الموصى بها

### للاختبار الأول:
```
1. ✅ تأكد من الملفات موجودة
2. ✅ مسح Cache
3. ✅ تأكد من الإعدادات
4. ✅ أضيف درس Zoom
5. ✅ شوف النتيجة
```

### للاختبار الكامل:
```
1. ✅ اختبر إضافة درس
2. ✅ اختبر تحديث درس
3. ✅ اختبر تعطيل Zoom
4. ✅ اختبر الأخطاء (API معطلة)
5. ✅ اختبر الإعادة والتصحيح
```

---

## 💡 نصائح

- اترك مجال **zoom_link فارغاً** عند الإضافة (سيملأ تلقائياً)
- استخدم **تاريخ مستقبلي** عند الاختبار
- شوف **storage/logs/laravel.log** عند الأخطاء
- استخدم **php artisan tinker** للتحقق من البيانات
- مسح **cache** بعد أي تغيير كود

---

**🚀 جاهز للاختبار! ابدأ الآن! 🚀**

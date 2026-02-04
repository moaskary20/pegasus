# إصلاح خطأ 403 وعدم ظهور صور الغلاف

عند ظهور خطأ **403 Forbidden** أو **عدم ظهور صورة الغلاف** بعد رفعها وحفظ الدورة:
- مثال: `https://academypegasus.com/storage/courses/covers/xxx.png` يعطي 403
- الصورة ترفع وتحفظ لكن لا تظهر بعد تحديث الصفحة

## الخطوات على السيرفر الخارجي

### 1. إنشاء رابط التخزين (مهم جداً)
```bash
cd /path/to/your/project
php artisan storage:link
```

### 2. صلاحيات المجلدات
```bash
# صلاحيات مجلد storage
chmod -R 755 storage
chmod -R 775 storage/app/public

# صلاحيات bootstrap (للكاش)
chmod -R 755 bootstrap/cache
```

### 3. ملكية الملفات (إذا كان السيرفر Linux)
```bash
# استبدل www-data باسم مستخدم الويب (قد يكون nginx أو apache)
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

---

## إذا استمر الخطأ

### Apache
تأكد أن السيرفر يسمح بـ `FollowSymLinks` في إعدادات VirtualHost:
```apache
<Directory /path/to/project/public>
    Options +FollowSymLinks -Indexes
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx
أضف هذا داخل بلوك `server`:
```nginx
location /storage {
    alias /path/to/your/project/storage/app/public;
    # أو إذا استخدمت الرابط الرمزي:
    # alias /path/to/your/project/public/storage;
}
```

---

## التحقق
بعد التنفيذ، تأكد من وجود الرابط:
```bash
ls -la public/storage
# يجب أن يظهر: storage -> ../storage/app/public
```

## إذا فشل storage:link (الرابط موجود مسبقاً)
```bash
# احذف الرابط القديم ثم أعد إنشاءه
rm -f public/storage
php artisan storage:link
```

## التأكد من رفع الملفات
```bash
# تحقق من وجود المجلد والملفات
ls -la storage/app/public/courses/covers/
# يجب أن تظهر الصور المرفوعة
```

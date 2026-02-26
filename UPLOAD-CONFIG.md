# إعداد رفع الفيديوهات الكبيرة (200 ميجا)

عند ظهور خطأ **"failed to upload"** لفيديو المعاينة، تأكد من الإعدادات التالية:

## 1. أباتشي (Apache)

يوجد إعداد في ملف `public/.htaccess` يعمل تلقائياً مع **mod_php** (PHP كوحدة من أباتشي).

إذا لم يعمل (مثلاً عند استخدام PHP-FPM)، الملف `public/.user.ini` سيُطبَّق تلقائياً. قد تحتاج لإعادة تشغيل PHP أو الانتظار 5 دقائق.

للتحقق من اسم وحدة PHP: `apache2ctl -M | grep php` أو `httpd -M | grep php`

## 2. Nginx

أضف أو عدّل في بلوك `server` داخل ملف إعدادات Nginx (مثلاً `/etc/nginx/sites-available/default`):

```nginx
client_max_body_size 220M;
client_body_buffer_size 220M;
client_body_timeout 600;
```

ثم أعد تشغيل Nginx:
```bash
sudo nginx -t && sudo systemctl restart nginx
```

## 3. PHP

عدّل `php.ini` أو أنشئ/عدّل `.user.ini` في مجلد `public/`:

```ini
upload_max_filesize = 220M
post_max_size = 220M
max_execution_time = 300
```

ثم أعد تشغيل PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

> **ملاحظة:** ملف `.user.ini` الموجود في `public/` يُقرأ تلقائياً، لكن قد يحتاج إعادة تشغيل PHP أو الانتظار بضع دقائق.

## 4. التحقق من الحدود الحالية

شغّل الأمر التالي لمعرفة حدود PHP الحالية:

```bash
php artisan upload:limits
```

أو افتح في المتصفح: `https://your-domain.com/upload-limits`

## 5. استضافة مشتركة

إذا كنت على استضافة مشتركة ولا تستطيع تعديل Nginx/PHP، اتصل بالدعم واطلب:
- رفع `client_max_body_size` إلى 220M
- رفع `upload_max_filesize` و `post_max_size` إلى 220M

## 6. بديل: رابط يوتيوب

يمكن استخدام **رابط يوتيوب** لمعاينة الدورة بدلاً من رفع ملف، دون الحاجة لتعديل أي إعدادات.

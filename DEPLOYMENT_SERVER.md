# الموقع لا يفتح على السيرفر الخارجي — استكشاف الأخطاء

عندما **لا يفتح الموقع** على السيرفر (مثلاً `https://academypegasus.com`): اتبع الخطوات التالية بالترتيب.

---

## 1. التأكد من مجلد التشغيل (Document Root)

يجب أن يكون **جذر الموقع (Document Root)** يشير إلى مجلد **`public`** داخل المشروع وليس إلى جذر المشروع.

### Nginx — مثال إعداد صحيح

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name academypegasus.com www.academypegasus.com;

    root /path/to/pegasus-academy/public;   # مهم: انتهاء المسار بـ public

    add_header X-Frame-Options "SAMEORIGIN";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;   # أو 127.0.0.1:9000
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

استبدل:
- `/path/to/pegasus-academy` بمسار المشروع الفعلي على السيرفر.
- `php8.2-fpm` بإصدار PHP المثبت لديك (مثلاً `php8.3-fpm`).

بعد التعديل:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Apache — مثال إعداد صحيح

```apache
<VirtualHost *:80>
    ServerName academypegasus.com
    DocumentRoot /path/to/pegasus-academy/public

    <Directory /path/to/pegasus-academy/public>
        Options +FollowSymLinks -Indexes
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 2. ملف `.env` على السيرفر

- تأكد أن ملف **`.env`** موجود في **جذر المشروع** (نفس مستوى `artisan`).
- إذا لم يكن موجوداً:
  ```bash
  cd /path/to/pegasus-academy
  cp .env.example .env
  php artisan key:generate
  ```
- عدّل في `.env` على الأقل:
  - `APP_URL=https://academypegasus.com` (أو النطاق الذي تستخدمه)
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - إعدادات قاعدة البيانات الصحيحة (`DB_*`).

---

## 3. الصلاحيات والملكية

```bash
cd /path/to/pegasus-academy

# صلاحيات التخزين والكاش
chmod -R 775 storage bootstrap/cache

# ملكية مستخدم الويب (غالباً www-data أو nginx)
sudo chown -R www-data:www-data storage bootstrap/cache
```

استبدل `www-data` بالمستخدم الذي يشغّل Nginx/Apache إذا كان مختلفاً.

---

## 4. تثبيت الاعتماديات والكاش

```bash
cd /path/to/pegasus-academy

composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

إذا كان المشروع يستخدم Vite وأصول مُجمّعة:
```bash
npm ci
npm run build
```

---

## 5. قاعدة البيانات والهجرات

- تأكد أن إعدادات **`DB_*`** في `.env` صحيحة وأن قاعدة البيانات تعمل.
- تشغيل الهجرات:
  ```bash
  php artisan migrate --force
  ```

---

## 6. صفحة بيضاء أو خطأ 500

- تفعيل السجلات مؤقتاً في `.env`:
  - `APP_DEBUG=true` و `LOG_LEVEL=debug`
- إعادة تحميل الصفحة ثم فحص السجل:
  ```bash
  tail -50 storage/logs/laravel.log
  ```
- غالباً ستجد سبب الخطأ (مثلاً: قاعدة بيانات، صلاحيات، امتداد PHP ناقص). بعد الإصلاح أرجع `APP_DEBUG=false` في الإنتاج.

---

## 7. HTTPS (إذا كان الموقع يعمل على HTTP لكن لا يعمل على HTTPS)

- تأكد أن شهادة SSL مفعّلة لـ `academypegasus.com`.
- في `.env` يجب أن يكون:
  - `APP_URL=https://academypegasus.com`
- إذا كان أمام Laravel **بروكسي (Nginx كبروكسي أو CDN)** قد تحتاج إلى إعداد Trust Proxies في Laravel حتى يتعرّف على HTTPS والـ Host الصحيح.

---

## 8. فحص سريع من السيرفر

```bash
# هل PHP يعمل؟
php -v

# هل المشروع يتحمّل؟
cd /path/to/pegasus-academy
php artisan --version

# هل الصفحة الرئيسية تُعاد من PHP؟
php artisan serve
# ثم من جهازك: curl http://السيرفر:8000
```

---

## 9. الجدار الناري والمنافذ

- تأكد أن منفذ **80** (HTTP) و **443** (HTTPS) مفتوحان في الجدار الناري على السيرفر وفي إعدادات مزوّد الاستضافة إن وُجدت.

---

## ملخص سريع

| المشكلة المحتملة        | ما تفعله |
|-------------------------|----------|
| Document Root خاطئ       | جعل الجذر = `.../public` في Nginx/Apache |
| لا يوجد `.env`          | نسخ من `.env.example` وتوليد `APP_KEY` |
| صلاحيات                 | `chmod 775` لـ `storage` و `bootstrap/cache` و `chown` لمستخدم الويب |
| خطأ 500 أو صفحة بيضاء   | مراجعة `storage/logs/laravel.log` وإعدادات DB و PHP |
| الموقع لا يفتح أصلاً   | التحقق من DNS و SSL والجدار الناري ومنافذ 80/443 |

إذا نفّذت الخطوات وما زال الموقع لا يفتح، أرسل آخر 30–50 سطراً من `storage/logs/laravel.log` ورسالة الخطأ التي تظهر في المتصفح (أو لقطة شاشة) لتحديد السبب بدقة.

# ⚡ QUICK COMMANDS REFERENCE

**Location:** Run these from Laravel project root (`/media/mohamed/d1/Pegasus Academy/`)

---

## 🚀 DEPLOYMENT COMMANDS

### 1. Backup Database (IMPORTANT!)
```bash
mysqldump -u your_user -p your_database > backup_$(date +%Y%m%d).sql
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Clear Cache (IMPORTANT!)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 4. (Optional) Optimize
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔍 VERIFICATION COMMANDS

### Check Database Connection
```bash
php artisan db:show
```

### Check Migrations Status
```bash
php artisan migrate:status
```

### Verify Database Structure
```bash
php artisan tinker
>>> DB::connection()->getPdo()
>>> PlatformSetting::where('group', 'zoom')->count()
>>> exit()
```

### Check Error Log
```bash
tail -f storage/logs/laravel.log
```

---

## 🛠️ TROUBLESHOOTING COMMANDS

### If Migration Fails - Rollback
```bash
php artisan migrate:rollback
```

### Restore from Backup
```bash
mysql -u your_user -p your_database < backup_20260129.sql
```

### Clear All Cache Files
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Fix File Permissions
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 database/
```

### Restart Services (if needed)
```bash
# For Apache + PHP-FPM
sudo systemctl restart php8.1-fpm

# For Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

# For Apache
sudo systemctl restart apache2
```

---

## 📊 MONITORING COMMANDS

### Monitor Error Logs in Real-Time
```bash
tail -f storage/logs/laravel.log
```

### Count Zoom Meetings
```bash
php artisan tinker
>>> ZoomMeeting::count()
>>> exit()
```

### Check Zoom Settings
```bash
php artisan tinker
>>> PlatformSetting::where('group', 'zoom')->pluck('key', 'value')
>>> exit()
```

---

## 🧪 TESTING COMMANDS

### Run All Tests
```bash
php artisan test
```

### Run Specific Test File
```bash
php artisan test tests/Feature/ZoomMeetingTest.php
```

### Run Unit Tests Only
```bash
php artisan test --unit
```

### Run Feature Tests Only
```bash
php artisan test --feature
```

---

## 🔙 ROLLBACK COMMANDS

### Rollback Last Migration
```bash
php artisan migrate:rollback --step=1
```

### Rollback All Migrations
```bash
php artisan migrate:rollback --step=10
```

### Rollback Specific Migration
```bash
php artisan migrate:rollback --target=2026_01_29_create_zoom_meetings_table.php
```

### Reset Database (CAREFUL!)
```bash
php artisan migrate:reset
```

---

## 🗄️ DATABASE COMMANDS

### Directly Query Database
```bash
mysql -u your_user -p your_database
# Then in MySQL:
SELECT * FROM zoom_meetings LIMIT 5;
SHOW COLUMNS FROM zoom_meetings;
SELECT COUNT(*) FROM platform_settings WHERE `group` = 'zoom';
EXIT;
```

### Backup Specific Table
```bash
mysqldump -u your_user -p your_database zoom_meetings > zoom_meetings_backup.sql
```

### Restore Specific Table
```bash
mysql -u your_user -p your_database < zoom_meetings_backup.sql
```

---

## 🔐 SECURITY COMMANDS

### Generate App Key (if needed)
```bash
php artisan key:generate
```

### Hash a Password
```bash
php artisan tinker
>>> Hash::make('your_password')
>>> exit()
```

---

## 📁 FILE MANAGEMENT COMMANDS

### List Modified Files
```bash
git status
```

### See All Changes
```bash
git diff
```

### Commit Changes
```bash
git add .
git commit -m "Zoom integration Phase 3"
git push origin main
```

### Check File Permissions
```bash
ls -la storage/
ls -la bootstrap/cache/
ls -la database/
```

---

## 📊 PERFORMANCE COMMANDS

### Check Laravel Config
```bash
php artisan config:cache
```

### Generate Autoloader
```bash
composer dumpautoload
```

### Optimize Autoloader
```bash
composer dumpautoload -o
```

---

## 🎯 ALL-IN-ONE DEPLOYMENT SCRIPT

Run this complete deployment in order:

```bash
#!/bin/bash
# Zoom Integration Deployment Script

echo "Starting Zoom Integration Deployment..."

# Backup database
echo "Creating database backup..."
mysqldump -u root -p pegasus_academy > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
echo "Running migrations..."
php artisan migrate

# Clear caches
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Fix permissions
echo "Fixing permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 database/

# Optimize
echo "Optimizing..."
php artisan optimize

# Verify
echo "Verifying installation..."
php artisan migrate:status

echo "✅ Deployment complete!"
echo "Check error logs: tail -f storage/logs/laravel.log"
```

Save as `deploy.sh` and run:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 📋 COMMAND CHEATSHEET

```
BACKUP:          mysqldump -u user -p db > backup.sql
MIGRATE:         php artisan migrate
CLEAR CACHE:     php artisan cache:clear
VERIFY:          php artisan migrate:status
LOGS:            tail -f storage/logs/laravel.log
ROLLBACK:        php artisan migrate:rollback
TINKER (Debug):  php artisan tinker
OPTIMIZE:        php artisan optimize
PERMISSIONS:     chmod -R 755 storage/
FIX ALL:         ./deploy.sh (use script above)
```

---

## ⚠️ IMPORTANT NOTES

1. **Always backup before migrating:**
   ```bash
   mysqldump -u user -p db > backup.sql
   ```

2. **Clear cache after changes:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. **Monitor logs during deployment:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Keep backups for rollback:**
   ```bash
   mysql -u user -p db < backup.sql
   ```

5. **Test in staging first:**
   - Use dev environment
   - Test with real team
   - Verify before production

---

## 🆘 EMERGENCY ROLLBACK

If something goes wrong:

```bash
# Step 1: Stop processes
sudo systemctl stop php8.1-fpm
# or
sudo systemctl stop apache2

# Step 2: Restore database
mysql -u user -p db < backup.sql

# Step 3: Restore code (if you have it)
git checkout HEAD~1
# or
cp -r ./laravel-backup/* ./laravel/

# Step 4: Restart services
sudo systemctl start php8.1-fpm
# or
sudo systemctl start apache2

# Step 5: Check logs
tail -f storage/logs/laravel.log
```

---

## 📞 HELP

For detailed instructions, see:
- `docs/DEPLOYMENT_GUIDE.md` - Full deployment guide
- `docs/FINAL_CHECKLIST.md` - QA procedures
- `docs/ZOOM_ROADMAP.md` - Architecture overview

---

**Last Updated:** 29 January 2026  
**For Use With:** Pegasus Academy Zoom Integration v1.0

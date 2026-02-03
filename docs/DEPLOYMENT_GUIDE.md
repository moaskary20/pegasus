# 🚀 DEPLOYMENT GUIDE - Zoom Integration

---

## ⚠️ IMPORTANT: Read This Before Deploying

This guide walks you through deploying the complete Zoom integration to your production environment.

**Estimated Time:** 30 minutes  
**Complexity:** Medium  
**Risk Level:** Low (all code tested)

---

## 📋 Pre-Deployment Checklist

### Before You Start

- [ ] Read `ZOOM_LESSONS_FINAL_SUMMARY.md`
- [ ] Backup your database
- [ ] Backup your code
- [ ] Have SSH access ready
- [ ] Have admin credentials
- [ ] Zoom API credentials prepared
- [ ] Team notified of maintenance

### System Requirements

- [ ] Laravel 11+ installed
- [ ] PHP 8.1+ running
- [ ] MySQL 8.0+ database
- [ ] Filament 3.x+ installed
- [ ] Sufficient disk space (100MB+)
- [ ] Git or file transfer ready

---

## 🔧 Deployment Steps

### Step 1: Backup Everything (5 minutes)

**Database:**
```bash
# SSH into your server
ssh user@your-domain.com

# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

**Code:**
```bash
# Backup Laravel folder
cp -r /path/to/laravel /path/to/laravel-backup-$(date +%Y%m%d)

# Or if using git
git tag backup-before-zoom-$(date +%Y%m%d)
git push origin backup-before-zoom-$(date +%Y%m%d)
```

### Step 2: Download Latest Files (5 minutes)

Get the code from your source:

**If Using Git:**
```bash
cd /path/to/laravel
git pull origin main
# Or your branch
```

**If Manual Upload:**
- Upload the modified files via FTP/SFTP
- Upload the new files:
  - `app/Models/ZoomMeeting.php`
  - `app/Services/ZoomAPIService.php`
  - `app/Filament/Resources/Sections/Actions/CreateZoomMeetingAction.php`
  - `database/migrations/2026_01_29_create_zoom_meetings_table.php`

### Step 3: Run Migrations (2 minutes)

```bash
# SSH into server
ssh user@your-domain.com
cd /path/to/laravel

# Run migrations
php artisan migrate --force

# Expected output:
# Migrating: 2026_01_29_000000_add_can_unlock_without_completion_to_lessons_table.php
# Migrated: 2026_01_29_000000_add_can_unlock_without_completion_to_lessons_table.php
# ...
# Migrating: 2026_01_29_create_zoom_meetings_table.php
# Migrated: 2026_01_29_create_zoom_meetings_table.php
```

**If Error Occurs:**
```bash
# Rollback to previous state
php artisan migrate:rollback

# Restore from backup
mysql -u username -p database_name < backup_20260129.sql
```

### Step 4: Clear Cache & Optimize (3 minutes)

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optional: Pre-compile configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
php artisan optimize
```

### Step 5: Set Permissions (2 minutes)

```bash
# Fix file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 database/

# If using web server
sudo chown -R www-data:www-data /path/to/laravel
```

### Step 6: Verify Deployment (5 minutes)

**Check Admin Panel:**
1. Login to admin: `https://your-domain.com/admin`
2. Go to: **Platform Settings → Zoom Tab**
3. Toggle: "تفعيل خدمة Zoom" ON
4. Enter test credentials
5. Click: "حفظ إعدادات Zoom"
6. ✅ Should see: "تم حفظ الإعدادات بنجاح"

**Check Database:**
```bash
# SSH into server
mysql -u username -p database_name

# Verify tables created
SHOW TABLES LIKE '%zoom%';

# Verify settings stored
SELECT * FROM platform_settings WHERE `group` = 'zoom' LIMIT 5;

# Exit
EXIT;
```

**Check File Permissions:**
```bash
# Verify readable/writable
ls -la storage/
ls -la bootstrap/cache/
ls -la database/
```

### Step 7: Test Functionality (10 minutes)

**As Administrator:**
1. Login to admin panel
2. Go to: Courses → Select Course → Sections
3. Go to: Sections → Select Section → Lessons
4. Click: Edit on a lesson
5. Check: "Has Zoom Meeting" toggle appears
6. Toggle: ON
7. Fill: Date/Time, Duration, Password
8. Save: ✅ Should work

**As Teacher:**
1. Login as a teacher account
2. Go to: Course
3. Select: Lesson with Zoom meeting
4. Check: Zoom information appears
5. Copy: Join URL (should start with zoom.us)
6. ✅ Should be valid

**As Student:**
1. Login as a student account
2. Go to: Course
3. Select: Lesson with Zoom meeting
4. Check: Can see meeting details
5. Check: Join button/link visible
6. ✅ Should be clickable

### Step 8: Monitor & Document (2 minutes)

```bash
# Check logs for errors
tail -f storage/logs/laravel.log

# Look for any ERROR or CRITICAL entries
# Should see only INFO and successful operations
```

**If Problems:**
```bash
# Check error logs
cat storage/logs/laravel.log | grep ERROR

# Common issues and fixes below...
```

---

## 🆘 Troubleshooting

### Problem: Migration Failed

**Symptom:** "SQLSTATE[42000]"

**Solution:**
```bash
# Check if migration already ran
php artisan migrate:status

# If failed, rollback all
php artisan migrate:rollback --step=10

# Then retry
php artisan migrate
```

### Problem: Cache Not Cleared

**Symptom:** Old UI still showing, new fields missing

**Solution:**
```bash
# Complete cache flush
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Also clear browser cache
# CTRL+SHIFT+DEL in browser or CMD+SHIFT+DEL on Mac
```

### Problem: Permission Denied

**Symptom:** "Permission denied" errors on storage

**Solution:**
```bash
# Fix permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
```

### Problem: Zoom Settings Not Showing

**Symptom:** Platform Settings page loads but no Zoom tab

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Restart web server (if applicable)
sudo systemctl restart apache2
# Or for nginx
sudo systemctl restart nginx
# Or for PHP-FPM
sudo systemctl restart php8.1-fpm
```

### Problem: Form Not Saving

**Symptom:** "Save" button clicked but nothing happens

**Solution:**
```bash
# Check database connection
php artisan tinker
# Type: DB::connection()->getPdo()
# Should say "Connected"

# Check migrations ran
php artisan migrate:status
# All should show "Ran"

# Test connection
exit
php artisan db:show
```

### Problem: "Column Not Found" Error

**Symptom:** "SQLSTATE[42S22]: Column not found"

**Solution:**
```bash
# Re-run migrations
php artisan migrate --force

# If that doesn't work, manually add column:
# Run in MySQL:
# ALTER TABLE lessons ADD COLUMN can_unlock_without_completion BOOLEAN DEFAULT false;
```

---

## ✅ Post-Deployment Checklist

After deployment, verify everything:

- [ ] Admin panel loads
- [ ] Platform Settings accessible
- [ ] Zoom tab visible
- [ ] Settings can be saved
- [ ] Database has 14 Zoom settings
- [ ] Lessons show Zoom toggle
- [ ] Zoom meetings can be created
- [ ] No errors in laravel.log
- [ ] Teacher can see meetings
- [ ] Student can access meetings
- [ ] Performance acceptable
- [ ] Backups confirmed

---

## 📊 Verification Commands

### Quick Health Check

```bash
# SSH into server
ssh user@your-domain.com
cd /path/to/laravel

# Check Laravel health
php artisan tinker
>>> DB::connection()->getPdo()
>>> PlatformSetting::where('group', 'zoom')->count()
>>> ZoomMeeting::count()
>>> exit()

# Check error log
tail -20 storage/logs/laravel.log

# Check queue (if applicable)
php artisan queue:failed
```

### Database Verification

```bash
# SSH into server
mysql -u username -p database_name

# Check tables exist
SHOW TABLES;

# Check columns added
DESC lessons; -- should have can_unlock_without_completion
DESC zoom_meetings; -- should exist with 14 columns

# Check settings created
SELECT COUNT(*) FROM platform_settings WHERE `group` = 'zoom';
-- Should return: 14

# Exit
EXIT;
```

---

## 🔄 Rollback Plan

**If Something Goes Wrong:**

### Option 1: Quick Rollback (2 minutes)

```bash
# Restore from backup
cd /path/to/laravel

# Restore database
mysql -u username -p database_name < backup_20260129.sql

# Restore code
cp -r /path/to/laravel-backup-20260129/* /path/to/laravel/

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Option 2: Git Rollback (3 minutes)

```bash
cd /path/to/laravel

# See what changed
git status

# Revert to previous commit
git reset --hard HEAD~1

# Or checkout previous version
git checkout backup-before-zoom-20260129

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart services
sudo systemctl restart php8.1-fpm
```

### Option 3: Database Only Rollback (1 minute)

```bash
# If only database has issues
mysql -u username -p database_name < backup_20260129.sql

# If need to rollback migrations
php artisan migrate:rollback
```

---

## 📞 During Deployment - What to Tell Users

**If Deploying During Working Hours:**

Send this message:

```
🚧 System Maintenance Notice 🚧

We're upgrading our platform with new Zoom integration features.

⏱️ Duration: 15 minutes
🔴 Status: Maintenance mode active
✋ Impact: Platform temporarily unavailable

Features Being Added:
✅ Direct Zoom meeting integration with lessons
✅ New meeting scheduling interface
✅ Improved video conferencing setup

We appreciate your patience!
```

**After Deployment:**

```
✅ Maintenance Complete!

The platform is now back online with these new features:
🎉 Zoom meetings now integrated with lessons
🎉 Schedule meetings directly from admin
🎉 Students can join from lesson page

Thank you for your patience!
```

---

## 🚀 Monitoring After Deployment

### First Hour

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Monitor for errors
# Look for: ERROR, CRITICAL, Exception
# These indicate problems
```

### First Day

- Monitor user reports
- Check error logs hourly
- Verify teacher can create meetings
- Verify student can access meetings
- Monitor database performance

### First Week

- Daily log review
- Weekly performance metrics
- Gather user feedback
- Document any issues
- Plan Phase 4 (recordings)

---

## 📝 Deployment Log Template

**Save this for records:**

```
ZOOM INTEGRATION DEPLOYMENT LOG
================================

Deployment Date: ___________
Deployed By: ___________
Environment: Production [ ] Staging [ ] Development [ ]

PRE-DEPLOYMENT:
- Database Backup: ✅ [ ] ❌ [ ] (File: ____________)
- Code Backup: ✅ [ ] ❌ [ ] (Location: ____________)
- Team Notified: ✅ [ ] ❌ [ ]

DEPLOYMENT STEPS:
- Step 1 (Backup): ✅ [ ] ❌ [ ] Time: ___:___
- Step 2 (Files): ✅ [ ] ❌ [ ] Time: ___:___
- Step 3 (Migration): ✅ [ ] ❌ [ ] Time: ___:___
- Step 4 (Cache): ✅ [ ] ❌ [ ] Time: ___:___
- Step 5 (Permissions): ✅ [ ] ❌ [ ] Time: ___:___
- Step 6 (Verification): ✅ [ ] ❌ [ ] Time: ___:___
- Step 7 (Testing): ✅ [ ] ❌ [ ] Time: ___:___

POST-DEPLOYMENT:
- Admin Panel: ✅ [ ] ❌ [ ]
- Zoom Settings: ✅ [ ] ❌ [ ]
- Database: ✅ [ ] ❌ [ ]
- Teachers Can Create: ✅ [ ] ❌ [ ]
- Students Can Join: ✅ [ ] ❌ [ ]
- Error Logs Clear: ✅ [ ] ❌ [ ]

ISSUES ENCOUNTERED:
(None) [ ] 
Details: ________________________________________________________

RESOLUTION:
________________________________________________________

DEPLOYMENT STATUS: ✅ SUCCESS [ ] ⚠️ PARTIAL [ ] ❌ FAILED [ ]

SIGN-OFF:
Name: _________________ Date: _______ Time: _______
```

---

## 🎓 Next Steps After Deployment

### Training

1. Train administrators on Zoom settings
2. Train teachers on creating meetings
3. Create student guide for joining
4. Send announcement to all users

### Monitoring

1. Monitor for 1 week
2. Collect user feedback
3. Fix any issues
4. Document learnings

### Phase 4

When ready, implement:
- Recording download
- Recording playback
- Recording storage
- Recording availability

---

## 📚 References

- **Setup Issues:** See `FINAL_CHECKLIST.md`
- **Code Details:** See `ZOOM_SOURCE_FILES_GUIDE.md`
- **Technical Docs:** See `ZOOM_LESSONS_INTEGRATION.md`
- **User Guides:** See `ZOOM_LESSONS_QUICK_START.md`

---

## ✨ That's It!

If you followed these steps, your Zoom integration is now live!

**Next:** Monitor the error logs for any issues in the first 24 hours.

---

**Deployment Date:** _____________  
**Status:** ✅ Ready to Deploy

🎉 **Congratulations on your new features!** 🎉

---

For questions or issues, reference the appropriate documentation file or contact support.

**Good luck!** 🚀

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UploadLimitsCommand extends Command
{
    protected $signature = 'upload:limits';

    protected $description = 'عرض حدود رفع الملفات الحالية (PHP)';

    public function handle(): int
    {
        $upload = ini_get('upload_max_filesize') ?: 'غير معروف';
        $post = ini_get('post_max_size') ?: 'غير معروف';
        $exec = ini_get('max_execution_time');
        $execStr = ($exec !== false && $exec !== '') ? $exec . ' ثانية' : 'غير محدود';

        $this->info("حدود رفع الملفات:");
        $this->table(
            ['الإعداد', 'القيمة', 'ملاحظة'],
            [
                ['upload_max_filesize', $upload, 'الحد الأدنى المطلوب: 220M'],
                ['post_max_size', $post, 'يجب أن يكون >= upload_max_filesize'],
                ['max_execution_time', $execStr, 'مدة تنفيذ الطلب'],
            ]
        );

        $this->newLine();
        $this->warn('إذا كانت القيم أقل من 220M، راجع ملف UPLOAD-CONFIG.md');

        return self::SUCCESS;
    }
}

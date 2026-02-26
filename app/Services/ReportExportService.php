<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class ReportExportService
{
    /**
     * Export sales report to CSV (Excel compatible)
     */
    public function exportSalesToCsv(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        
        $query = Order::with(['user', 'items.course'])
            ->where('status', 'paid');
        
        if (!$user->hasRole('admin')) {
            $query->whereHas('items.course', fn($q) => $q->where('user_id', $user->id));
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        if (isset($filters['course_id']) && $filters['course_id'] !== 'all') {
            $query->whereHas('items', fn($q) => $q->where('course_id', $filters['course_id']));
        }
        
        $orders = $query->orderByDesc('created_at')->get();
        
        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        return Response::stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($handle, [
                'رقم الطلب',
                'التاريخ',
                'العميل',
                'البريد الإلكتروني',
                'الدورة',
                'المبلغ',
                'الحالة',
            ]);
            
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->user?->name ?? 'غير معروف',
                    $order->user?->email ?? '',
                    $order->items->first()?->course?->title ?? 'غير معروف',
                    number_format($order->total, 2),
                    'مدفوع',
                ]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
    
    /**
     * Export student progress to CSV
     */
    public function exportProgressToCsv(array $filters = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        
        $query = Enrollment::with(['user', 'course'])
            ->whereHas('course', function ($q) use ($user) {
                if (!$user->hasRole('admin')) {
                    $q->where('user_id', $user->id);
                }
            });
        
        if (isset($filters['course_id']) && $filters['course_id'] !== 'all') {
            $query->where('course_id', $filters['course_id']);
        }
        
        if (isset($filters['progress_filter'])) {
            match ($filters['progress_filter']) {
                'completed' => $query->whereNotNull('completed_at'),
                'in_progress' => $query->whereNull('completed_at')->where('progress_percentage', '>', 0),
                'not_started' => $query->where('progress_percentage', 0)->orWhereNull('progress_percentage'),
                default => null,
            };
        }
        
        $enrollments = $query->orderByDesc('updated_at')->get();
        
        $filename = 'student_progress_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        return Response::stream(function () use ($enrollments) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($handle, [
                'الطالب',
                'البريد الإلكتروني',
                'الدورة',
                'نسبة التقدم',
                'الحالة',
                'تاريخ التسجيل',
                'تاريخ الإكمال',
            ]);
            
            foreach ($enrollments as $enrollment) {
                $status = match (true) {
                    $enrollment->completed_at !== null => 'مكتمل',
                    ($enrollment->progress_percentage ?? 0) > 0 => 'قيد التقدم',
                    default => 'لم يبدأ',
                };
                
                fputcsv($handle, [
                    $enrollment->user?->name ?? 'غير معروف',
                    $enrollment->user?->email ?? '',
                    $enrollment->course?->title ?? 'غير معروف',
                    round($enrollment->progress_percentage ?? 0) . '%',
                    $status,
                    $enrollment->created_at->format('Y-m-d'),
                    $enrollment->completed_at?->format('Y-m-d') ?? '-',
                ]);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
    
    /**
     * Generate sales summary for PDF
     */
    public function getSalesSummary(array $filters = []): array
    {
        $user = auth()->user();
        
        $query = Order::where('status', 'paid');
        
        if (!$user->hasRole('admin')) {
            $query->whereHas('items.course', fn($q) => $q->where('user_id', $user->id));
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        return [
            'total_revenue' => (clone $query)->sum('total'),
            'total_orders' => (clone $query)->count(),
            'average_order' => (clone $query)->avg('total') ?? 0,
        ];
    }
}

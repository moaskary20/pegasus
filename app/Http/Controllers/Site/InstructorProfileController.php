<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorProfileController extends Controller
{
    /**
     * عرض صفحة الملف الشخصي للمدرب
     */
    public function show(User $instructor): View
    {
        // التأكد من أن المستخدم لديه دورات منشورة
        $hasPublishedCourses = Course::query()
            ->where('user_id', $instructor->id)
            ->where('is_published', true)
            ->exists();

        if (!$hasPublishedCourses) {
            abort(404);
        }

        $courses = Course::query()
            ->where('user_id', $instructor->id)
            ->where('is_published', true)
            ->with(['category', 'subCategory'])
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->get();

        $totalStudents = (int) $courses->sum('students_count');
        $coursesCount = $courses->count();

        return view('instructors.show', [
            'instructor' => $instructor,
            'courses' => $courses,
            'coursesCount' => $coursesCount,
            'totalStudents' => $totalStudents,
        ]);
    }
}

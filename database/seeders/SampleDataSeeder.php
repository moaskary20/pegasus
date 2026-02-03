<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الأدوار إذا لم تكن موجودة
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // إنشاء تصنيفات
        $categories = [
            ['name' => 'البرمجة', 'slug' => 'programming', 'icon' => 'code'],
            ['name' => 'التصميم', 'slug' => 'design', 'icon' => 'palette'],
            ['name' => 'التسويق', 'slug' => 'marketing', 'icon' => 'megaphone'],
            ['name' => 'الأعمال', 'slug' => 'business', 'icon' => 'briefcase'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => 'وصف ' . $cat['name'],
                    'icon' => $cat['icon'],
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }

        // إنشاء مدرسين
        $instructors = [];
        $instructorNames = [
            'أحمد محمد',
            'فاطمة علي',
            'محمد حسن',
            'سارة إبراهيم',
        ];

        foreach ($instructorNames as $index => $name) {
            $email = 'instructor' . ($index + 1) . '@example.com';
            $instructor = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('password'),
                    'phone' => '0123456789' . $index,
                    'city' => ['القاهرة', 'الإسكندرية', 'الجيزة', 'المنصورة'][$index],
                    'job' => 'مدرس ' . ['برمجة', 'تصميم', 'تسويق', 'أعمال'][$index],
                    'email_verified_at' => now(),
                ]
            );
            if (!$instructor->hasRole($instructorRole)) {
                $instructor->assignRole($instructorRole);
            }
            $instructors[] = $instructor;
        }

        // إنشاء طلاب
        $students = [];
        $studentNames = [
            'علي أحمد',
            'مريم خالد',
            'يوسف محمود',
            'نورا سعيد',
            'خالد فؤاد',
            'ليلى عمرو',
        ];

        foreach ($studentNames as $index => $name) {
            $email = 'student' . ($index + 1) . '@example.com';
            $student = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('password'),
                    'phone' => '0112345678' . $index,
                    'city' => ['القاهرة', 'الإسكندرية', 'الجيزة', 'المنصورة', 'أسيوط', 'طنطا'][$index],
                    'job' => 'طالب',
                    'email_verified_at' => now(),
                ]
            );
            if (!$student->hasRole($studentRole)) {
                $student->assignRole($studentRole);
            }
            $students[] = $student;
        }

        // إنشاء دورات
        $coursesData = [
            [
                'title' => 'تعلم البرمجة من الصفر',
                'description' => 'دورة شاملة لتعلم البرمجة من الأساسيات إلى المستوى المتقدم',
                'level' => 'beginner',
                'price' => 500,
                'offer_price' => 350,
                'hours' => 40,
                'lectures_count' => 25,
                'category' => 'programming',
            ],
            [
                'title' => 'تصميم الجرافيك المتقدم',
                'description' => 'تعلم تصميم الجرافيك باستخدام أحدث الأدوات والتقنيات',
                'level' => 'intermediate',
                'price' => 600,
                'offer_price' => 450,
                'hours' => 35,
                'lectures_count' => 20,
                'category' => 'design',
            ],
            [
                'title' => 'التسويق الرقمي الشامل',
                'description' => 'إتقان فنون التسويق الرقمي ووسائل التواصل الاجتماعي',
                'level' => 'beginner',
                'price' => 400,
                'offer_price' => 300,
                'hours' => 30,
                'lectures_count' => 18,
                'category' => 'marketing',
            ],
            [
                'title' => 'إدارة الأعمال الحديثة',
                'description' => 'تعلم إدارة الأعمال والمشاريع بطرق حديثة وفعالة',
                'level' => 'advanced',
                'price' => 700,
                'offer_price' => 550,
                'hours' => 45,
                'lectures_count' => 28,
                'category' => 'business',
            ],
        ];

        $courses = [];
        foreach ($coursesData as $index => $courseData) {
            $category = Category::where('slug', $courseData['category'])->first();
            $slug = Str::slug($courseData['title']);
            
            $course = Course::firstOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $instructors[$index % count($instructors)]->id,
                    'title' => $courseData['title'],
                    'description' => $courseData['description'],
                    'objectives' => 'أهداف الدورة: فهم الأساسيات، تطبيق المهارات، بناء المشاريع',
                    'level' => $courseData['level'],
                    'price' => $courseData['price'],
                    'offer_price' => $courseData['offer_price'],
                    'hours' => $courseData['hours'],
                    'lectures_count' => $courseData['lectures_count'],
                    'category_id' => $category->id,
                    'is_published' => true,
                    'students_count' => 0,
                    'rating' => rand(40, 50) / 10,
                    'reviews_count' => rand(10, 50),
                ]
            );
            $courses[] = $course;
        }

        // إنشاء أقسام ودروس لكل دورة
        $sectionTitles = [
            ['المقدمة والأساسيات', 'المفاهيم المتوسطة', 'المستوى المتقدم', 'المشاريع العملية'],
            ['أساسيات التصميم', 'الأدوات والتقنيات', 'التصميم المتقدم', 'المشاريع الإبداعية'],
            ['مقدمة التسويق', 'منصات التواصل', 'الإعلانات المدفوعة', 'التحليل والقياس'],
            ['مقدمة إدارة الأعمال', 'التخطيط الاستراتيجي', 'القيادة والإدارة', 'دراسات الحالة'],
        ];

        $lessonTitles = [
            [
                ['مقدمة الدورة', 'ما هي البرمجة؟', 'أدوات البرمجة', 'أول برنامج لك'],
                ['المتغيرات', 'الشروط', 'الحلقات', 'الدوال'],
                ['البرمجة الكائنية', 'قواعد البيانات', 'واجهات المستخدم', 'API'],
                ['مشروع 1', 'مشروع 2', 'مشروع 3', 'مراجعة نهائية'],
            ],
            [
                ['مقدمة التصميم', 'مبادئ التصميم', 'الألوان والخطوط', 'التكوين'],
                ['أدوبي فوتوشوب', 'أدوبي إليستريتور', 'أدوبي إنديزاين', 'أدوات أخرى'],
                ['التصميم ثلاثي الأبعاد', 'الرسوم المتحركة', 'التصميم التفاعلي', 'الهوية البصرية'],
                ['تصميم شعار', 'تصميم بوستر', 'تصميم موقع', 'معرض أعمال'],
            ],
            [
                ['ما هو التسويق الرقمي؟', 'استراتيجية التسويق', 'الجمهور المستهدف', 'التحليل'],
                ['فيسبوك وإنستجرام', 'تويتر ولينكد إن', 'يوتيوب', 'تيك توك'],
                ['إعلانات جوجل', 'إعلانات فيسبوك', 'التسويق بالمحتوى', 'التسويق بالبريد'],
                ['تحليل الأداء', 'تحسين الحملات', 'ROI', 'دراسات حالة'],
            ],
            [
                ['مقدمة إدارة الأعمال', 'أنواع الشركات', 'الهيكل التنظيمي', 'الموارد البشرية'],
                ['التخطيط الاستراتيجي', 'إدارة الميزانية', 'إدارة المخاطر', 'الجودة'],
                ['القيادة', 'إدارة الفرق', 'التواصل', 'حل المشكلات'],
                ['دراسة حالة 1', 'دراسة حالة 2', 'دراسة حالة 3', 'الخلاصة'],
            ],
        ];

        foreach ($courses as $courseIndex => $course) {
            $sections = [];
            foreach ($sectionTitles[$courseIndex] as $sectionIndex => $sectionTitle) {
                $section = Section::create([
                    'course_id' => $course->id,
                    'title' => $sectionTitle,
                    'description' => 'وصف ' . $sectionTitle,
                    'sort_order' => $sectionIndex + 1,
                ]);
                $sections[] = $section;

                // إنشاء دروس لكل قسم
                foreach ($lessonTitles[$courseIndex][$sectionIndex] as $lessonIndex => $lessonTitle) {
                    $lesson = Lesson::create([
                        'section_id' => $section->id,
                        'title' => $lessonTitle,
                        'description' => 'وصف ' . $lessonTitle,
                        'content' => '<p>محتوى ' . $lessonTitle . '</p>',
                        'content_type' => 'video',
                        'duration_minutes' => rand(15, 45),
                        'sort_order' => $lessonIndex + 1,
                        'is_free_preview' => $lessonIndex === 0, // أول درس مجاني
                        'is_free' => false,
                    ]);

                    // إنشاء اختبار لبعض الدروس
                    if ($lessonIndex % 2 === 0) {
                        $quiz = Quiz::create([
                            'lesson_id' => $lesson->id,
                            'title' => 'اختبار: ' . $lessonTitle,
                            'description' => 'اختبار لقياس فهمك لـ ' . $lessonTitle,
                            'duration_minutes' => 30,
                            'pass_percentage' => 60,
                            'allow_retake' => true,
                            'max_attempts' => 3,
                            'randomize_questions' => false,
                            'questions_count' => 5,
                        ]);

                        // إنشاء أسئلة للاختبار
                        $questionTypes = ['mcq', 'true_false', 'fill_blank'];
                        for ($q = 1; $q <= 5; $q++) {
                            $type = $questionTypes[array_rand($questionTypes)];
                            $options = [];
                            $correctAnswer = [];

                            if ($type === 'mcq') {
                                $options = ['الخيار الأول', 'الخيار الثاني', 'الخيار الثالث', 'الخيار الرابع'];
                                $correctAnswer = [rand(0, 3)];
                            } elseif ($type === 'true_false') {
                                $options = ['صحيح', 'خطأ'];
                                $correctAnswer = [rand(0, 1)];
                            } else { // fill_blank
                                $options = null;
                                $correctAnswer = ['إجابة نموذجية'];
                            }

                            QuizQuestion::create([
                                'quiz_id' => $quiz->id,
                                'type' => $type,
                                'question_text' => 'سؤال ' . $q . ' في ' . $lessonTitle . '؟',
                                'options' => $options,
                                'correct_answer' => $correctAnswer,
                                'explanation' => 'شرح الإجابة الصحيحة',
                                'points' => 20,
                                'sort_order' => $q,
                            ]);
                        }
                    }

                    // إنشاء أسئلة للدورة من بعض الطلاب
                    if ($lessonIndex % 3 === 0 && count($students) > 0) {
                        $student = $students[array_rand($students)];
                        CourseQuestion::create([
                            'course_id' => $course->id,
                            'user_id' => $student->id,
                            'lesson_id' => $lesson->id,
                            'question' => 'سؤال من ' . $student->name . ' حول ' . $lessonTitle,
                            'is_answered' => rand(0, 1) === 1,
                        ]);
                    }
                }
            }
        }

        // إنشاء تسجيلات للطلاب في الدورات
        foreach ($students as $studentIndex => $student) {
            // كل طالب يسجل في دورة أو دورتين
            $coursesToEnroll = array_slice($courses, 0, min(2, count($courses)));
            foreach ($coursesToEnroll as $course) {
                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'price_paid' => $course->offer_price,
                    'enrolled_at' => now()->subDays(rand(1, 30)),
                    'progress_percentage' => rand(0, 100),
                ]);

                // تحديث عدد الطلاب في الدورة
                $course->increment('students_count');
            }
        }

        $this->command->info('تم إنشاء البيانات التجريبية بنجاح!');
        $this->command->info('المدرسين: ' . count($instructors));
        $this->command->info('الطلاب: ' . count($students));
        $this->command->info('الدورات: ' . count($courses));
        $this->command->info('التسجيلات: ' . Enrollment::count());
    }
}

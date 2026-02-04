<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الأقسام الرئيسية (Categories)
        $mainCategories = [
            [
                'name' => 'كتب تعليمية',
                'slug' => 'educational-books',
                'description' => 'كتب ومراجع تعليمية في مختلف المجالات',
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'name' => 'أدوات مكتبية',
                'slug' => 'office-supplies',
                'description' => 'أدوات ومستلزمات مكتبية للدراسة والعمل',
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'name' => 'إلكترونيات',
                'slug' => 'electronics',
                'description' => 'أجهزة إلكترونية ومعدات تقنية',
                'sort_order' => 3,
                'is_featured' => true,
            ],
            [
                'name' => 'منتجات رقمية',
                'slug' => 'digital-products',
                'description' => 'قوالب وكورسات رقمية للتحميل',
                'sort_order' => 4,
                'is_featured' => false,
            ],
            [
                'name' => 'ملابس وهوية',
                'slug' => 'merchandise',
                'description' => 'ملابس وهدايا تحمل شعار الأكاديمية',
                'sort_order' => 5,
                'is_featured' => false,
            ],
        ];

        $createdCategories = [];
        foreach ($mainCategories as $cat) {
            $createdCategories[$cat['slug']] = ProductCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['is_active' => true])
            );
        }

        // إنشاء أقسام فرعية
        $subCategories = [
            [
                'name' => 'كتب البرمجة',
                'slug' => 'programming-books',
                'parent_slug' => 'educational-books',
                'sort_order' => 1,
            ],
            [
                'name' => 'كتب التصميم',
                'slug' => 'design-books',
                'parent_slug' => 'educational-books',
                'sort_order' => 2,
            ],
            [
                'name' => 'أجهزة كمبيوتر',
                'slug' => 'computers',
                'parent_slug' => 'electronics',
                'sort_order' => 1,
            ],
            [
                'name' => 'إكسسوارات',
                'slug' => 'accessories',
                'parent_slug' => 'electronics',
                'sort_order' => 2,
            ],
        ];

        foreach ($subCategories as $sub) {
            $parent = $createdCategories[$sub['parent_slug']] ?? null;
            $createdCategories[$sub['slug']] = ProductCategory::firstOrCreate(
                ['slug' => $sub['slug']],
                [
                    'name' => $sub['name'],
                    'description' => 'وصف ' . $sub['name'],
                    'parent_id' => $parent?->id,
                    'sort_order' => $sub['sort_order'],
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );
        }

        // إنشاء المنتجات
        $productsData = [
            // كتب تعليمية
            [
                'name' => 'كتاب تعلم Python من الصفر',
                'short_description' => 'دليل شامل لتعلم لغة Python للمبتدئين',
                'description' => '<p>كتاب شامل يغطي أساسيات البرمجة بلغة Python مع أمثلة عملية ومشاريع تطبيقية. مناسب للمبتدئين وطلاب البرمجة.</p>',
                'price' => 120,
                'compare_price' => 150,
                'quantity' => 50,
                'category_slug' => 'programming-books',
                'is_featured' => true,
            ],
            [
                'name' => 'كتاب تصميم الواجهات UX/UI',
                'short_description' => 'أساسيات تصميم تجربة المستخدم',
                'description' => '<p>كتاب متخصص في تصميم واجهات المستخدم وتجربة المستخدم مع أفضل الممارسات والأمثلة العملية.</p>',
                'price' => 95,
                'compare_price' => 120,
                'quantity' => 30,
                'category_slug' => 'design-books',
                'is_featured' => true,
            ],
            [
                'name' => 'كتاب التسويق الرقمي',
                'short_description' => 'دليل التسويق عبر الإنترنت',
                'description' => '<p>مرجع شامل للتسويق الرقمي يشمل وسائل التواصل الاجتماعي والإعلانات والتحليلات.</p>',
                'price' => 85,
                'compare_price' => 100,
                'quantity' => 40,
                'category_slug' => 'educational-books',
                'is_featured' => false,
            ],
            [
                'name' => 'كتاب قواعد البيانات SQL',
                'short_description' => 'تعلم SQL وقواعد البيانات',
                'description' => '<p>كتاب تعليمي يغطي لغة SQL وقواعد البيانات العلائقية من الأساسيات إلى المستوى المتقدم.</p>',
                'price' => 75,
                'compare_price' => 90,
                'quantity' => 25,
                'category_slug' => 'programming-books',
                'is_featured' => false,
            ],

            // أدوات مكتبية
            [
                'name' => 'دفتر ملاحظات أكاديمي',
                'short_description' => 'دفتر 200 صفحة خطوط',
                'description' => '<p>دفتر ملاحظات عالي الجودة 200 صفحة بخطوط منظمة، مناسب للدراسة وتدوين الملاحظات.</p>',
                'price' => 25,
                'compare_price' => 35,
                'quantity' => 200,
                'category_slug' => 'office-supplies',
                'is_featured' => true,
            ],
            [
                'name' => 'طقم أقلام ملونة 24 لون',
                'short_description' => 'أقلام ملونة عالية الجودة',
                'description' => '<p>طقم أقلام ملونة 24 لون من ماركة معروفة، مناسبة للرسم والتلوين والتمارين.</p>',
                'price' => 45,
                'compare_price' => 60,
                'quantity' => 80,
                'category_slug' => 'office-supplies',
                'is_featured' => false,
            ],
            [
                'name' => 'حقيبة كمبيوتر محمول',
                'short_description' => 'حقيبة واقية للابتوب',
                'description' => '<p>حقيبة أنيقة ومريحة لحمل الكمبيوتر المحمول مع جيوب إضافية للمستلزمات.</p>',
                'price' => 180,
                'compare_price' => 220,
                'quantity' => 35,
                'category_slug' => 'office-supplies',
                'is_featured' => true,
            ],
            [
                'name' => 'منظم مكتب خشبي',
                'short_description' => 'منظم للمستلزمات المكتبية',
                'description' => '<p>منظم مكتب خشبي أنيق لترتيب الأقلام والأوراق والمستلزمات الصغيرة.</p>',
                'price' => 65,
                'compare_price' => 80,
                'quantity' => 45,
                'category_slug' => 'office-supplies',
                'is_featured' => false,
            ],

            // إلكترونيات
            [
                'name' => 'سماعات رأس للدراسة',
                'short_description' => 'سماعات عزل صوت ممتاز',
                'description' => '<p>سماعات رأس مريحة مع عزل صوت ممتاز، مثالية للدراسة والمحاضرات أونلاين.</p>',
                'price' => 250,
                'compare_price' => 320,
                'quantity' => 60,
                'category_slug' => 'accessories',
                'is_featured' => true,
            ],
            [
                'name' => 'كاميرا ويب HD',
                'short_description' => 'كاميرا للمحاضرات والتسجيل',
                'description' => '<p>كاميرا ويب بدقة HD مع ميكروفون مدمج، مناسبة للمحاضرات والتسجيل.</p>',
                'price' => 450,
                'compare_price' => 550,
                'quantity' => 25,
                'category_slug' => 'accessories',
                'is_featured' => true,
            ],
            [
                'name' => 'لوحة مفاتيح لاسلكية',
                'short_description' => 'لوحة مفاتيح عربية وإنجليزية',
                'description' => '<p>لوحة مفاتيح لاسلكية بتصميم عربي/إنجليزي مع دعم اختصارات الكتابة العربية.</p>',
                'price' => 180,
                'compare_price' => 220,
                'quantity' => 40,
                'category_slug' => 'computers',
                'is_featured' => false,
            ],
            [
                'name' => 'ماوس لاسلكي ergonomic',
                'short_description' => 'ماوس مريح للعمل الطويل',
                'description' => '<p>ماوس لاسلكي بتصميم ergonomic يقلل إجهاد اليد أثناء العمل لفترات طويلة.</p>',
                'price' => 120,
                'compare_price' => 150,
                'quantity' => 55,
                'category_slug' => 'computers',
                'is_featured' => false,
            ],

            // منتجات رقمية
            [
                'name' => 'قوالب PowerPoint احترافية',
                'short_description' => 'حزمة 20 قالب عرض تقديمي',
                'description' => '<p>حزمة من 20 قالب PowerPoint احترافي جاهز للاستخدام في العروض التقديمية.</p>',
                'price' => 99,
                'compare_price' => 150,
                'quantity' => 0,
                'category_slug' => 'digital-products',
                'is_featured' => true,
                'is_digital' => true,
                'track_quantity' => false,
            ],
            [
                'name' => 'كورس Excel متقدم - تحميل',
                'short_description' => 'فيديوهات تعليمية لـ Excel',
                'description' => '<p>كورس فيديو متقدم لتعلم Excel مع الملفات العملية للتحميل.</p>',
                'price' => 199,
                'compare_price' => 299,
                'quantity' => 0,
                'category_slug' => 'digital-products',
                'is_featured' => true,
                'is_digital' => true,
                'track_quantity' => false,
            ],
            [
                'name' => 'قوالب تصميم Canva',
                'short_description' => '50 قالب للتصميم الجرافيكي',
                'description' => '<p>مجموعة من 50 قالب Canva جاهز للتصميم الجرافيكي والشبكات الاجتماعية.</p>',
                'price' => 79,
                'compare_price' => 120,
                'quantity' => 0,
                'category_slug' => 'digital-products',
                'is_featured' => false,
                'is_digital' => true,
                'track_quantity' => false,
            ],

            // ملابس وهوية
            [
                'name' => 'تيشيرت أكاديمية بيجاسوس',
                'short_description' => 'تيشيرت قطني بجودة عالية',
                'description' => '<p>تيشيرت قطني 100% بطباعة شعار أكاديمية بيجاسوس، مقاسات متعددة.</p>',
                'price' => 120,
                'compare_price' => 150,
                'quantity' => 100,
                'category_slug' => 'merchandise',
                'is_featured' => true,
            ],
            [
                'name' => 'كوب مخصص بالأكاديمية',
                'short_description' => 'كوب حراري 350 مل',
                'description' => '<p>كوب حراري سعة 350 مل مع طباعة شعار الأكاديمية، يحافظ على درجة الحرارة.</p>',
                'price' => 55,
                'compare_price' => 70,
                'quantity' => 75,
                'category_slug' => 'merchandise',
                'is_featured' => false,
            ],
            [
                'name' => 'حقيبة قماش مخصصة',
                'short_description' => 'حقيبة قماش للطلاب',
                'description' => '<p>حقيبة قماش قوية بحجم A4 مع شعار الأكاديمية، مناسبة للطلاب.</p>',
                'price' => 85,
                'compare_price' => 110,
                'quantity' => 50,
                'category_slug' => 'merchandise',
                'is_featured' => false,
            ],
        ];

        foreach ($productsData as $productData) {
            $categorySlug = $productData['category_slug'];
            $category = $createdCategories[$categorySlug] ?? null;

            unset($productData['category_slug']);

            $slug = \Illuminate\Support\Str::slug($productData['name']);
            $productData['category_id'] = $category?->id;
            $productData['is_active'] = true;
            $productData['requires_shipping'] = !($productData['is_digital'] ?? false);
            $productData['track_quantity'] = $productData['track_quantity'] ?? true;

            if ($productData['is_digital'] ?? false) {
                $productData['quantity'] = 0;
            }

            Product::firstOrCreate(
                ['slug' => $slug],
                $productData
            );
        }

        $this->command->info('تم إنشاء بيانات المتجر بنجاح!');
        $this->command->info('الأقسام: ' . ProductCategory::count());
        $this->command->info('المنتجات: ' . Product::count());
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Categories (Main & Sub)
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable(); // Original price for discounts
            $table->decimal('cost_price', 10, 2)->nullable(); // Cost for profit calculation
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('main_image')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // in grams
            $table->string('dimensions')->nullable(); // LxWxH
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false); // Digital product
            $table->string('digital_file')->nullable(); // For digital products
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('track_quantity')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('ratings_count')->default(0);
            $table->json('meta_data')->nullable(); // For additional attributes
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Product Images (Multiple images per product)
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
        
        // Product Variants (Size, Color, etc.)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Large - Red"
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Override product price
            $table->integer('quantity')->default(0);
            $table->json('attributes')->nullable(); // {"size": "L", "color": "Red"}
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Product Reviews
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->json('pros')->nullable(); // Advantages
            $table->json('cons')->nullable(); // Disadvantages
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
        });
        
        // Store Orders
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled, refunded
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            
            // Customer Info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            
            // Shipping Address
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->default('مصر');
            $table->string('shipping_postal_code')->nullable();
            
            // Billing Address (optional)
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            
            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            $table->string('coupon_code')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Store Order Items
        Schema::create('store_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total', 10, 2);
            $table->json('options')->nullable();
            $table->timestamps();
        });
        
        // Shopping Cart
        Schema::create('store_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            $table->index(['user_id', 'session_id']);
        });
        
        // Wishlist
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id']);
        });
        
        // Store Coupons
        Schema::create('store_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_order', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('per_user_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_products')->nullable();
            $table->timestamps();
        });
        
        // Store Settings
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json, array
            $table->string('group')->default('general'); // general, shipping, payment, tax, notifications
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Shipping Zones
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('cities')->nullable(); // List of cities in this zone
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Shipping Methods
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('shipping_zones')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['flat', 'per_item', 'per_weight', 'free']);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('minimum_order_for_free', 10, 2)->nullable();
            $table->integer('estimated_days_min')->nullable();
            $table->integer('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        
        // Insert default store settings
        $this->insertDefaultSettings();
    }
    
    protected function insertDefaultSettings(): void
    {
        $settings = [
            // General Settings
            ['key' => 'store_name', 'value' => 'متجر أكاديمية بيجاسوس', 'type' => 'text', 'group' => 'general', 'label' => 'اسم المتجر'],
            ['key' => 'store_description', 'value' => 'متجر الأدوات والمنتجات التعليمية', 'type' => 'text', 'group' => 'general', 'label' => 'وصف المتجر'],
            ['key' => 'store_email', 'value' => 'store@pegasus.com', 'type' => 'text', 'group' => 'general', 'label' => 'البريد الإلكتروني'],
            ['key' => 'store_phone', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'رقم الهاتف'],
            ['key' => 'store_address', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'العنوان'],
            ['key' => 'currency', 'value' => 'EGP', 'type' => 'text', 'group' => 'general', 'label' => 'العملة'],
            ['key' => 'currency_symbol', 'value' => 'ج.م', 'type' => 'text', 'group' => 'general', 'label' => 'رمز العملة'],
            
            // Shipping Settings
            ['key' => 'enable_shipping', 'value' => 'true', 'type' => 'boolean', 'group' => 'shipping', 'label' => 'تفعيل الشحن'],
            ['key' => 'default_shipping_cost', 'value' => '50', 'type' => 'number', 'group' => 'shipping', 'label' => 'تكلفة الشحن الافتراضية'],
            ['key' => 'free_shipping_threshold', 'value' => '500', 'type' => 'number', 'group' => 'shipping', 'label' => 'الحد الأدنى للشحن المجاني'],
            ['key' => 'shipping_calculation', 'value' => 'flat', 'type' => 'text', 'group' => 'shipping', 'label' => 'طريقة حساب الشحن'],
            
            // Tax Settings
            ['key' => 'enable_tax', 'value' => 'false', 'type' => 'boolean', 'group' => 'tax', 'label' => 'تفعيل الضرائب'],
            ['key' => 'tax_rate', 'value' => '14', 'type' => 'number', 'group' => 'tax', 'label' => 'نسبة الضريبة (%)'],
            ['key' => 'tax_included_in_price', 'value' => 'true', 'type' => 'boolean', 'group' => 'tax', 'label' => 'الضريبة مشمولة في السعر'],
            
            // Order Settings
            ['key' => 'order_prefix', 'value' => 'ORD-', 'type' => 'text', 'group' => 'orders', 'label' => 'بادئة رقم الطلب'],
            ['key' => 'auto_confirm_orders', 'value' => 'false', 'type' => 'boolean', 'group' => 'orders', 'label' => 'تأكيد الطلبات تلقائياً'],
            ['key' => 'allow_guest_checkout', 'value' => 'true', 'type' => 'boolean', 'group' => 'orders', 'label' => 'السماح بالشراء كزائر'],
            ['key' => 'min_order_amount', 'value' => '0', 'type' => 'number', 'group' => 'orders', 'label' => 'الحد الأدنى للطلب'],
            
            // Inventory Settings
            ['key' => 'track_inventory', 'value' => 'true', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'تتبع المخزون'],
            ['key' => 'low_stock_notification', 'value' => 'true', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'إشعار انخفاض المخزون'],
            ['key' => 'out_of_stock_visibility', 'value' => 'true', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'إظهار المنتجات غير المتوفرة'],
            ['key' => 'allow_backorders', 'value' => 'false', 'type' => 'boolean', 'group' => 'inventory', 'label' => 'السماح بالطلب المسبق'],
            
            // Notification Settings
            ['key' => 'notify_admin_new_order', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications', 'label' => 'إشعار المدير بالطلبات الجديدة'],
            ['key' => 'notify_customer_order_status', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications', 'label' => 'إشعار العميل بحالة الطلب'],
        ];
        
        $now = now();
        foreach ($settings as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }
        
        \DB::table('store_settings')->insert($settings);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('store_coupons');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('store_carts');
        Schema::dropIfExists('store_order_items');
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Instructor payout settings - commission rates and payment info
        Schema::create('instructor_payout_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_rate', 5, 2)->default(70.00); // Default 70% for instructor
            $table->decimal('admin_fee_rate', 5, 2)->default(5.00); // Admin processing fee
            $table->decimal('minimum_payout', 10, 2)->default(100.00); // Minimum amount for payout
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, paypal, vodafone_cash
            $table->json('payment_details')->nullable(); // Bank account, PayPal email, etc.
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            $table->unique('user_id');
        });
        
        // Payout requests from instructors
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('requested_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2); // Instructor's share
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2); // Final amount to be paid
            $table->string('status')->default('pending'); // pending, approved, processing, completed, rejected
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
        
        // Payment vouchers - receipts for completed payments
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->foreignId('payout_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('transaction_reference')->nullable();
            $table->json('payment_proof')->nullable(); // Screenshots, receipts
            $table->text('notes')->nullable();
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['user_id', 'issued_at']);
        });
        
        // Earning transactions - track each sale contribution
        Schema::create('earning_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Instructor
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->decimal('sale_amount', 10, 2); // Original sale
            $table->decimal('commission_rate', 5, 2); // Rate at time of sale
            $table->decimal('commission_amount', 10, 2); // Instructor's share
            $table->decimal('platform_amount', 10, 2); // Platform's share
            $table->string('status')->default('available'); // available, pending_payout, paid_out
            $table->foreignId('payout_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_transactions');
        Schema::dropIfExists('payment_vouchers');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('instructor_payout_settings');
    }
};

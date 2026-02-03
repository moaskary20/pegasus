<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('zoom_meeting_id')->unique()->nullable();
            $table->string('topic');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_start_time');
            $table->integer('duration')->default(60); // بالدقائق
            $table->string('timezone')->default('Africa/Cairo');
            $table->longText('join_url')->nullable();
            $table->longText('start_url')->nullable();
            $table->string('password')->nullable();
            $table->string('host_id')->nullable();
            $table->enum('status', [
                'pending',      // لم يتم الإنشاء بعد
                'scheduled',    // تم جدولته
                'started',      // بدأ الاجتماع
                'ended',        // انتهى الاجتماع
                'cancelled'     // تم الإلغاء
            ])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index('lesson_id');
            $table->index('status');
            $table->index('scheduled_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->foreignId('room_id')->constrained('rooms');
            $table->foreignId('class_type_id')->nullable()->constrained('class_types')->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status', 20)->default('pending');
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['studio_id', 'student_id', 'start_at'], 'private_bookings_student_time');
            $table->index(['studio_id', 'teacher_id', 'start_at', 'end_at'], 'private_bookings_teacher_time');
            $table->index(['studio_id', 'room_id', 'start_at', 'end_at'], 'private_bookings_room_time');
            $table->index(['studio_id', 'status', 'start_at'], 'private_bookings_status_time');
            $table->unique(['studio_id', 'room_id', 'start_at'], 'private_bookings_room_start_unique');
            $table->unique(['studio_id', 'teacher_id', 'start_at'], 'private_bookings_teacher_start_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_bookings');
    }
};

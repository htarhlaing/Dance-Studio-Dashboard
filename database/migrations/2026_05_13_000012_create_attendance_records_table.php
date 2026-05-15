<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('private_booking_id')->constrained('private_bookings')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->foreignId('room_id')->constrained('rooms');
            $table->string('status', 20);
            $table->dateTime('checked_in_at');
            $table->foreignId('checked_in_by_user_id')->constrained('users');
            $table->dateTime('voided_at')->nullable();
            $table->foreignId('voided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('private_booking_id', 'attendance_private_booking_unique');
            $table->index(['studio_id', 'status', 'checked_in_at'], 'attendance_studio_status_time');
            $table->index(['studio_id', 'student_id', 'checked_in_at'], 'attendance_student_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};

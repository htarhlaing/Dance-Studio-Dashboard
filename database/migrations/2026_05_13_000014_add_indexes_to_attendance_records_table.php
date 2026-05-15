<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index(['studio_id', 'teacher_id', 'checked_in_at'], 'attendance_teacher_time');
            $table->index(['studio_id', 'room_id', 'checked_in_at'], 'attendance_room_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex('attendance_teacher_time');
            $table->dropIndex('attendance_room_time');
        });
    }
};

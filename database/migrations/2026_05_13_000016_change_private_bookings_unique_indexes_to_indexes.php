<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('private_bookings', function (Blueprint $table) {
            $table->dropUnique('private_bookings_room_start_unique');
            $table->dropUnique('private_bookings_teacher_start_unique');

            $table->index(['studio_id', 'room_id', 'start_at'], 'private_bookings_room_start_index');
            $table->index(['studio_id', 'teacher_id', 'start_at'], 'private_bookings_teacher_start_index');
        });
    }

    public function down(): void
    {
        Schema::table('private_bookings', function (Blueprint $table) {
            $table->dropIndex('private_bookings_room_start_index');
            $table->dropIndex('private_bookings_teacher_start_index');

            $table->unique(['studio_id', 'room_id', 'start_at'], 'private_bookings_room_start_unique');
            $table->unique(['studio_id', 'teacher_id', 'start_at'], 'private_bookings_teacher_start_unique');
        });
    }
};

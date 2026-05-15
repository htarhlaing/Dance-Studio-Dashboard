<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regular_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('class_type_id')->constrained('class_types');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['studio_id', 'room_id', 'day_of_week', 'start_time', 'end_time'], 'regular_classes_room_time');
            $table->index(['studio_id', 'teacher_id', 'day_of_week', 'start_time', 'end_time'], 'regular_classes_teacher_time');
            $table->index(['studio_id', 'starts_on', 'ends_on', 'is_active'], 'regular_classes_effective');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regular_classes');
    }
};

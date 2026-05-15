<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('student_package_id')->constrained('student_packages');
            $table->string('type', 32);
            $table->integer('units_delta');
            $table->unsignedInteger('balance_after');
            $table->dateTime('occurred_at');
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['studio_id', 'student_id', 'occurred_at'], 'pkg_tx_student_time');
            $table->index(['studio_id', 'student_package_id', 'occurred_at'], 'pkg_tx_package_time');
            $table->index(['studio_id', 'type', 'occurred_at'], 'pkg_tx_type_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_transactions');
    }
};

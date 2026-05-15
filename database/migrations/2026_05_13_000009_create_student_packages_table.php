<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('package_type_id')->constrained('package_types');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->dateTime('purchased_at');
            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('total_units');
            $table->unsignedInteger('remaining_units');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['studio_id', 'student_id', 'status', 'expires_at']);
            $table->index(['studio_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_packages');
    }
};

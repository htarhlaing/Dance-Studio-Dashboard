<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('method', 32);
            $table->string('status', 32)->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['studio_id', 'student_id', 'paid_at']);
            $table->index(['studio_id', 'status', 'created_at']);
            $table->unique(['studio_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

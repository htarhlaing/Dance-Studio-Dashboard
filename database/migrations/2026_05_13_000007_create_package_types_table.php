<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('lessons_count');
            $table->unsignedSmallInteger('validity_days')->nullable();
            $table->decimal('price', 12, 2);
            $table->char('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['studio_id', 'is_active']);
            $table->unique(['studio_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_types');
    }
};

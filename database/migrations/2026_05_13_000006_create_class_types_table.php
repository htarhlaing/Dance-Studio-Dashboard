<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained('studios')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('kind', 20)->default('both');
            $table->unsignedSmallInteger('default_duration_minutes')->nullable();
            $table->unsignedSmallInteger('default_deduct_units')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['studio_id', 'kind']);
            $table->unique(['studio_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_types');
    }
};

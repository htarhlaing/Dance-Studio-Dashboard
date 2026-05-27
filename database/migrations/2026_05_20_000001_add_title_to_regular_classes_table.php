<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regular_classes', function (Blueprint $table) {
            $table->string('title', 120)->nullable()->after('class_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('regular_classes', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};


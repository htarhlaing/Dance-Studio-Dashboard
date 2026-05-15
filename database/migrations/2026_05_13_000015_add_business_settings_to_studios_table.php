<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->time('business_start_time')->nullable();
            $table->time('business_end_time')->nullable();
            $table->integer('booking_interval_minutes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn(['business_start_time', 'business_end_time', 'booking_interval_minutes']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->unsignedInteger('minimum_booking_notice_hours')->default(48);
            $table->unsignedInteger('minimum_reschedule_notice_hours')->default(48);
            $table->unsignedInteger('minimum_cancel_notice_hours')->default(24);
            $table->boolean('allow_admin_frontdesk_override_notice')->default(true);
            $table->boolean('teacher_can_create_booking')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('studios', function (Blueprint $table) {
            $table->dropColumn([
                'minimum_booking_notice_hours',
                'minimum_reschedule_notice_hours',
                'minimum_cancel_notice_hours',
                'allow_admin_frontdesk_override_notice',
                'teacher_can_create_booking',
            ]);
        });
    }
};


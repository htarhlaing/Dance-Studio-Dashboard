<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioBookingRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $frontDesk;
    private User $teacher1User;
    private Teacher $teacher1;
    private int $studioId;
    private Room $room;
    private int $privateClassTypeId;
    private int $studentIdWithActivePackage;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00'));

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->frontDesk = User::query()->where('email', 'frontdesk@example.com')->firstOrFail();
        $this->teacher1User = User::query()->where('email', 'teacher1@example.com')->firstOrFail();
        $this->teacher1 = Teacher::query()->where('user_id', $this->teacher1User->id)->firstOrFail();

        $this->studioId = (int) $this->admin->studio_id;
        $this->room = Room::query()->where('studio_id', $this->studioId)->orderBy('id')->firstOrFail();

        $this->privateClassTypeId = (int) ClassType::query()
            ->where('studio_id', $this->studioId)
            ->where('is_active', true)
            ->where('kind', 'private')
            ->orderBy('id')
            ->value('id');

        if ($this->privateClassTypeId <= 0) {
            $this->privateClassTypeId = (int) ClassType::query()
                ->where('studio_id', $this->studioId)
                ->where('is_active', true)
                ->where('name', 'Private Lesson')
                ->orderBy('id')
                ->value('id');
        }

        $this->studentIdWithActivePackage = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_studio_can_save_minimum_booking_notice_hours(): void
    {
        $this->actingAs($this->admin)
            ->post('/studio-settings', [
                'name' => 'Demo Dance Studio',
                'business_start_time' => '08:30',
                'business_end_time' => '20:30',
                'booking_interval_minutes' => 90,
                'timezone' => 'Asia/Yangon',
                'currency' => 'MMK',
                'private_lesson_duration_minutes' => 90,
                'minimum_booking_notice_hours' => 12,
                'minimum_reschedule_notice_hours' => 24,
                'minimum_cancel_notice_hours' => 6,
                'allow_admin_frontdesk_override_notice' => '1',
                'teacher_can_create_booking' => '1',
            ])
            ->assertSessionHasNoErrors();

        $studio = Studio::query()->findOrFail($this->studioId);
        $this->assertSame(12, (int) $studio->minimum_booking_notice_hours);
        $this->assertSame(24, (int) $studio->minimum_reschedule_notice_hours);
        $this->assertSame(6, (int) $studio->minimum_cancel_notice_hours);
        $this->assertTrue((bool) $studio->allow_admin_frontdesk_override_notice);
        $this->assertTrue((bool) $studio->teacher_can_create_booking);
    }

    public function test_teacher_cannot_create_when_teacher_can_create_booking_is_false(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'teacher_can_create_booking' => false,
            'minimum_booking_notice_hours' => 0,
        ]);

        $response = $this->actingAs($this->teacher1User)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-25',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_teacher_can_create_when_enabled_but_must_respect_minimum_booking_notice_hours(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'teacher_can_create_booking' => true,
            'minimum_booking_notice_hours' => 48,
        ]);

        $response = $this->actingAs($this->teacher1User)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);

        $response = $this->actingAs($this->teacher1User)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-23',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_admin_front_desk_override_can_bypass_notice_if_enabled(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'minimum_booking_notice_hours' => 48,
            'allow_admin_frontdesk_override_notice' => true,
        ]);

        $response = $this->actingAs($this->frontDesk)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_admin_front_desk_must_respect_notice_if_override_disabled(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'minimum_booking_notice_hours' => 48,
            'allow_admin_frontdesk_override_notice' => false,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_all_roles_cannot_create_past_time_booking(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'teacher_can_create_booking' => true,
            'minimum_booking_notice_hours' => 0,
            'allow_admin_frontdesk_override_notice' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-20',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_reschedule_uses_minimum_reschedule_notice_hours_and_override_flag(): void
    {
        $booking = PrivateBooking::query()->create([
            'studio_id' => $this->studioId,
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'start_at' => '2026-05-23 10:00:00',
            'end_at' => '2026-05-23 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        Studio::query()->whereKey($this->studioId)->update([
            'minimum_reschedule_notice_hours' => 48,
            'allow_admin_frontdesk_override_notice' => false,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/reschedule', [
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
        ]);
        $response->assertSessionHasErrors(['booking']);

        Studio::query()->whereKey($this->studioId)->update([
            'allow_admin_frontdesk_override_notice' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/reschedule', [
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
        ]);
        $response->assertRedirect('/private-bookings');
    }

    public function test_cancel_uses_minimum_cancel_notice_hours_and_override_flag(): void
    {
        $booking = PrivateBooking::query()->create([
            'studio_id' => $this->studioId,
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->studentIdWithActivePackage,
            'class_type_id' => $this->privateClassTypeId,
            'start_at' => '2026-05-21 08:00:00',
            'end_at' => '2026-05-21 09:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        Studio::query()->whereKey($this->studioId)->update([
            'minimum_cancel_notice_hours' => 24,
            'allow_admin_frontdesk_override_notice' => false,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/cancel');
        $response->assertSessionHasErrors(['booking']);
        $booking->refresh();
        $this->assertSame('pending', $booking->status);

        Studio::query()->whereKey($this->studioId)->update([
            'allow_admin_frontdesk_override_notice' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/cancel');
        $response->assertRedirect('/private-bookings');
        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
    }
}

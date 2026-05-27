<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PackageType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateBookingConflictTest extends TestCase
{
    use RefreshDatabase;

    private Studio $studio;

    private User $admin;

    private Room $roomA;

    private Room $roomB;

    private Teacher $teacherA;

    private Teacher $teacherB;

    private Student $studentSuSu;

    private Student $studentDavid;

    private ClassType $privateClassType;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(CarbonImmutable::parse('2026-05-20 09:00:00'));

        $this->studio = Studio::query()->create([
            'name' => 'Demo Dance Studio',
            'timezone' => 'Asia/Yangon',
            'currency' => 'MMK',
            'business_start_time' => '08:30:00',
            'business_end_time' => '20:30:00',
            'booking_interval_minutes' => 90,
            'is_active' => true,
        ]);

        $this->admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'studio_id' => $this->studio->id,
            'role' => 'admin',
        ]);

        $this->roomA = Room::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Studio A',
            'is_active' => true,
        ]);

        $this->roomB = Room::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Studio B',
            'is_active' => true,
        ]);

        $teacherAUser = User::query()->create([
            'name' => 'Teacher A Account',
            'email' => 'teacher1@example.com',
            'password' => bcrypt('password'),
            'studio_id' => $this->studio->id,
            'role' => 'teacher',
        ]);

        $teacherBUser = User::query()->create([
            'name' => 'Teacher B Account',
            'email' => 'teacher2@example.com',
            'password' => bcrypt('password'),
            'studio_id' => $this->studio->id,
            'role' => 'teacher',
        ]);

        $this->teacherA = Teacher::query()->create([
            'studio_id' => $this->studio->id,
            'user_id' => $teacherAUser->id,
            'display_name' => 'Teacher A',
            'is_active' => true,
        ]);

        $this->teacherB = Teacher::query()->create([
            'studio_id' => $this->studio->id,
            'user_id' => $teacherBUser->id,
            'display_name' => 'Teacher B',
            'is_active' => true,
        ]);

        $this->studentSuSu = Student::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Su Su',
            'is_active' => true,
        ]);

        $this->studentDavid = Student::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'David',
            'is_active' => true,
        ]);

        $this->privateClassType = ClassType::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Private Lesson',
            'kind' => 'private',
            'default_duration_minutes' => 90,
            'default_deduct_units' => 1,
            'is_active' => true,
        ]);

        $packageType = PackageType::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Private 8 Lessons',
            'lessons_count' => 8,
            'validity_days' => null,
            'price' => 0,
            'currency' => 'MMK',
            'is_active' => true,
        ]);

        StudentPackage::query()->create([
            'studio_id' => $this->studio->id,
            'student_id' => $this->studentSuSu->id,
            'package_type_id' => $packageType->id,
            'purchased_at' => '2026-05-01 00:00:00',
            'expires_at' => null,
            'total_units' => 8,
            'remaining_units' => 8,
            'status' => 'active',
        ]);

        StudentPackage::query()->create([
            'studio_id' => $this->studio->id,
            'student_id' => $this->studentDavid->id,
            'package_type_id' => $packageType->id,
            'purchased_at' => '2026-05-01 00:00:00',
            'expires_at' => null,
            'total_units' => 8,
            'remaining_units' => 8,
            'status' => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_can_create_private_booking(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherA->id,
                'student_id' => $this->studentSuSu->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('private_bookings', [
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'status' => 'pending',
        ]);
    }

    public function test_same_room_same_time_conflict_is_blocked(): void
    {
        // Create first booking: Room A, 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Try booking same room, overlapping time, different teacher & student
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertSessionHasErrors('booking');

        // Only 1 booking should exist
        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_same_teacher_same_time_conflict_is_blocked(): void
    {
        // Create first booking: Teacher A in Room A, 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Try booking same teacher, different room, overlapping time
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomB->id,
                'teacher_id' => $this->teacherA->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_completed_booking_still_blocks_same_time_slot(): void
    {
        // Create a completed booking: Room A, 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'completed',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Try booking same room/time — should be blocked
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_cancelled_booking_allows_same_time_slot(): void
    {
        // Create a cancelled booking: Room A, 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'cancelled',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Try booking same room/time — should succeed (cancelled releases slot)
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // 2 bookings now: 1 cancelled + 1 new
        $this->assertDatabaseCount('private_bookings', 2);

        $this->assertDatabaseHas('private_bookings', [
            'room_id' => $this->roomA->id,
            'student_id' => $this->studentDavid->id,
            'status' => 'pending',
        ]);
    }

    public function test_different_room_same_time_is_allowed(): void
    {
        // Booking A: Room A, Teacher A, 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Booking B: Room B, Teacher B, 10:00-11:30 — different room & teacher → allowed
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomB->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'duration' => 90,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('private_bookings', 2);
    }

    public function test_partial_time_overlap_is_detected(): void
    {
        // Existing booking: 10:00-11:30
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // Try booking same room, 11:00-12:30 — overlaps 11:00-11:30
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '11:00',
                'end_time' => '12:30',
                'duration' => 90,
            ]);

        $response->assertSessionHasErrors('booking');

        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_adjacent_time_no_overlap_is_allowed(): void
    {
        // Existing booking: 10:00-11:30 (end_at = 11:30)
        PrivateBooking::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'student_id' => $this->studentSuSu->id,
            'class_type_id' => $this->privateClassType->id,
            'start_at' => '2026-05-20 10:00:00',
            'end_at' => '2026-05-20 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        // New booking: same room, 11:30-13:00 — starts exactly when previous ends → no overlap
        $response = $this->actingAs($this->admin)
            ->post('/private-bookings', [
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacherB->id,
                'student_id' => $this->studentDavid->id,
                'class_type_id' => $this->privateClassType->id,
                'date' => '2026-05-20',
                'start_time' => '11:30',
                'end_time' => '13:00',
                'duration' => 90,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('private_bookings', 2);
    }
}

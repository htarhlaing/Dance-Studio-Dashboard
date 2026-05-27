<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\AttendanceRecord;
use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PackageTransaction;
use App\Models\DanceStudio\PackageType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDeductionTest extends TestCase
{
    use RefreshDatabase;

    private Studio $studio;

    private User $admin;

    private Room $roomA;

    private Teacher $teacherA;

    private Student $studentSuSu;

    private ClassType $privateClassType;

    private PackageType $packageType;

    private StudentPackage $studentPackage;

    protected function setUp(): void
    {
        parent::setUp();

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

        $teacherUser = User::query()->create([
            'name' => 'Teacher A Account',
            'email' => 'teacher1@example.com',
            'password' => bcrypt('password'),
            'studio_id' => $this->studio->id,
            'role' => 'teacher',
        ]);

        $this->teacherA = Teacher::query()->create([
            'studio_id' => $this->studio->id,
            'user_id' => $teacherUser->id,
            'display_name' => 'Teacher A',
            'is_active' => true,
        ]);

        $this->studentSuSu = Student::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Su Su',
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

        $this->packageType = PackageType::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Private 8 Lessons',
            'lessons_count' => 8,
            'price' => 0,
            'currency' => 'MMK',
            'is_active' => true,
        ]);

        $this->studentPackage = StudentPackage::query()->create([
            'studio_id' => $this->studio->id,
            'student_id' => $this->studentSuSu->id,
            'package_type_id' => $this->packageType->id,
            'purchased_at' => now(),
            'total_units' => 8,
            'remaining_units' => 8,
            'status' => 'active',
        ]);
    }

    public function test_present_creates_attendance_record(): void
    {
        $booking = $this->createPendingBooking();

        $response = $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendance_records', [
            'private_booking_id' => $booking->id,
            'student_id' => $this->studentSuSu->id,
            'teacher_id' => $this->teacherA->id,
            'room_id' => $this->roomA->id,
            'status' => 'present',
        ]);
    }

    public function test_present_deducts_student_package_remaining_units(): void
    {
        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        // remaining_units should go from 8 to 7
        $this->studentPackage->refresh();
        $this->assertEquals(7, $this->studentPackage->remaining_units);
    }

    public function test_present_creates_package_deduction_transaction(): void
    {
        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        $attendanceRecord = AttendanceRecord::query()
            ->where('private_booking_id', $booking->id)
            ->first();

        $this->assertNotNull($attendanceRecord);

        $this->assertDatabaseHas('package_transactions', [
            'student_id' => $this->studentSuSu->id,
            'student_package_id' => $this->studentPackage->id,
            'type' => 'deduction',
            'units_delta' => -1,
            'balance_after' => 7,
            'attendance_record_id' => $attendanceRecord->id,
        ]);
    }

    public function test_present_changes_booking_status_to_completed(): void
    {
        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    public function test_duplicate_present_does_not_deduct_twice(): void
    {
        $booking = $this->createPendingBooking();

        // First present — should succeed
        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        // Second present — should be blocked (idempotency)
        $response = $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        $response->assertSessionHasErrors('attendance');

        // Only 1 deduction should have happened
        $this->studentPackage->refresh();
        $this->assertEquals(7, $this->studentPackage->remaining_units);

        // Only 1 attendance record
        $this->assertDatabaseCount('attendance_records', 1);

        // Only 1 deduction transaction
        $this->assertEquals(1, PackageTransaction::query()
            ->where('student_package_id', $this->studentPackage->id)
            ->where('type', 'deduction')
            ->count());
    }

    public function test_absent_does_not_deduct_package(): void
    {
        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'absent',
            ]);

        // remaining_units stays at 8
        $this->studentPackage->refresh();
        $this->assertEquals(8, $this->studentPackage->remaining_units);

        // No deduction transaction
        $this->assertEquals(0, PackageTransaction::query()
            ->where('student_package_id', $this->studentPackage->id)
            ->where('type', 'deduction')
            ->count());

        // Booking still goes to completed
        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    public function test_cancelled_attendance_sets_booking_to_cancelled(): void
    {
        $booking = $this->createPendingBooking();

        $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'cancelled',
            ]);

        // No deduction
        $this->studentPackage->refresh();
        $this->assertEquals(8, $this->studentPackage->remaining_units);

        // Booking becomes cancelled
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);

        // Attendance record exists
        $this->assertDatabaseHas('attendance_records', [
            'private_booking_id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_present_without_active_package_throws_error(): void
    {
        // Deactivate the package
        $this->studentPackage->update(['status' => 'expired', 'remaining_units' => 0]);

        $booking = $this->createPendingBooking();

        $response = $this->actingAs($this->admin)
            ->post("/attendance/private-bookings/{$booking->id}/mark", [
                'status' => 'present',
            ]);

        $response->assertSessionHasErrors('attendance');

        // No attendance record created
        $this->assertDatabaseMissing('attendance_records', [
            'private_booking_id' => $booking->id,
        ]);

        // Booking stays pending
        $booking->refresh();
        $this->assertEquals('pending', $booking->status);
    }

    private function createPendingBooking(): PrivateBooking
    {
        return PrivateBooking::query()->create([
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
    }
}

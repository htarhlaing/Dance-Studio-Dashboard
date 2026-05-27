<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateBookingRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $frontDesk;
    private User $teacher1User;
    private Teacher $teacher1;
    private Room $room;
    private int $studioId;
    private int $privateClassTypeId;

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
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_student_without_active_package_cannot_create_private_booking(): void
    {
        $student = Student::query()->create([
            'studio_id' => $this->studioId,
            'name' => 'No Package Student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $student->id,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-22',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_active_package_with_remaining_units_can_create_private_booking(): void
    {
        $studentWithPackageId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $studentWithPackageId,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-20',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_expired_package_cannot_create_private_booking(): void
    {
        $student = Student::query()->create([
            'studio_id' => $this->studioId,
            'name' => 'Expired Package Student',
            'is_active' => true,
        ]);

        $packageTypeId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->orderBy('id')
            ->value('package_type_id');

        StudentPackage::query()->create([
            'studio_id' => $this->studioId,
            'student_id' => $student->id,
            'package_type_id' => $packageTypeId,
            'purchased_at' => CarbonImmutable::parse('2026-05-01 00:00:00'),
            'expires_at' => CarbonImmutable::parse('2026-05-21 00:00:00'),
            'total_units' => 4,
            'remaining_units' => 4,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $student->id,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-22',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_zero_remaining_units_cannot_create_private_booking(): void
    {
        $student = Student::query()->create([
            'studio_id' => $this->studioId,
            'name' => 'Zero Remaining Student',
            'is_active' => true,
        ]);

        $packageTypeId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->orderBy('id')
            ->value('package_type_id');

        StudentPackage::query()->create([
            'studio_id' => $this->studioId,
            'student_id' => $student->id,
            'package_type_id' => $packageTypeId,
            'purchased_at' => CarbonImmutable::parse('2026-05-01 00:00:00'),
            'expires_at' => null,
            'total_units' => 4,
            'remaining_units' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $student->id,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-20',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_teacher_cannot_create_booking_within_48_hours(): void
    {
        Studio::query()->whereKey($this->studioId)->update([
            'teacher_can_create_booking' => true,
            'minimum_booking_notice_hours' => 48,
        ]);

        $studentWithPackageId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');

        $response = $this->actingAs($this->teacher1User)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $studentWithPackageId,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-22',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_admin_can_create_booking_within_48_hours_but_in_future(): void
    {
        $studentWithPackageId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');

        $response = $this->actingAs($this->admin)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $studentWithPackageId,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-21',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('private_bookings', 1);
    }

    public function test_all_roles_cannot_create_past_date_booking(): void
    {
        $studentWithPackageId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');

        $response = $this->actingAs($this->frontDesk)->post('/private-bookings', [
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $studentWithPackageId,
            'class_type_id' => $this->privateClassTypeId,
            'date' => '2026-05-19',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'duration' => 90,
        ]);

        $response->assertSessionHasErrors(['booking']);
        $this->assertDatabaseCount('private_bookings', 0);
    }

    public function test_past_pending_booking_displays_pending_attendance(): void
    {
        $studentWithPackageId = (int) StudentPackage::query()
            ->where('studio_id', $this->studioId)
            ->where('status', 'active')
            ->where('remaining_units', '>', 0)
            ->orderBy('id')
            ->value('student_id');

        PrivateBooking::query()->create([
            'studio_id' => $this->studioId,
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $studentWithPackageId,
            'class_type_id' => $this->privateClassTypeId,
            'start_at' => '2026-05-19 10:00:00',
            'end_at' => '2026-05-19 11:30:00',
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/private-bookings?date=2026-05-19');
        $response->assertOk();
        $response->assertSee('Pending Attendance');
        $response->assertDontSee('Completed');
    }
}

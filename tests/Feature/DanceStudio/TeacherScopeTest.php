<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherScopeTest extends TestCase
{
    use RefreshDatabase;

    private int $studioId;
    private User $admin;
    private User $frontDesk;
    private User $teacher1User;
    private User $teacher2User;
    private Teacher $teacher1;
    private Teacher $teacher2;
    private Room $room;
    private Student $student1;
    private Student $student2;
    private int $privateClassTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->studioId = (int) Studio::query()->where('name', 'Demo Dance Studio')->value('id');
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->frontDesk = User::query()->where('email', 'frontdesk@example.com')->firstOrFail();
        $this->teacher1User = User::query()->where('email', 'teacher1@example.com')->firstOrFail();
        $this->teacher2User = User::query()->where('email', 'teacher2@example.com')->firstOrFail();

        $this->teacher1 = Teacher::query()->where('user_id', $this->teacher1User->id)->firstOrFail();
        $this->teacher2 = Teacher::query()->where('user_id', $this->teacher2User->id)->firstOrFail();

        $this->room = Room::query()->where('studio_id', $this->studioId)->orderBy('id')->firstOrFail();
        $this->student1 = Student::query()->where('studio_id', $this->studioId)->orderBy('id')->firstOrFail();
        $this->student2 = Student::query()->where('studio_id', $this->studioId)->orderByDesc('id')->firstOrFail();

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

    private function seedTwoBookings(string $date): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $startAt = $day->setTimeFromTimeString('10:00');
        $endAt = $day->setTimeFromTimeString('11:30');

        $booking1 = PrivateBooking::query()->create([
            'studio_id' => $this->studioId,
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher1->id,
            'student_id' => $this->student1->id,
            'class_type_id' => $this->privateClassTypeId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        $booking2 = PrivateBooking::query()->create([
            'studio_id' => $this->studioId,
            'room_id' => $this->room->id,
            'teacher_id' => $this->teacher2->id,
            'student_id' => $this->student2->id,
            'class_type_id' => $this->privateClassTypeId,
            'start_at' => $startAt->addHours(2),
            'end_at' => $endAt->addHours(2),
            'status' => 'pending',
            'requested_by_user_id' => $this->admin->id,
        ]);

        return [$booking1, $booking2];
    }

    public function test_teacher_only_sees_own_private_bookings(): void
    {
        $date = '2026-05-25';
        $this->seedTwoBookings($date);

        $response = $this->actingAs($this->teacher1User)->get('/private-bookings?date='.$date);
        $response->assertOk();
        $response->assertSee('Teacher A');
        $response->assertDontSee('Teacher B');
    }

    public function test_teacher_only_sees_own_attendance_list(): void
    {
        $date = '2026-05-25';
        $this->seedTwoBookings($date);

        $response = $this->actingAs($this->teacher1User)->get('/attendance?date='.$date);
        $response->assertOk();
        $response->assertSee('Teacher A');
        $response->assertDontSee('Teacher B');
    }

    public function test_teacher_cannot_mark_attendance_for_other_teachers_booking(): void
    {
        $date = '2026-05-25';
        [, $booking2] = $this->seedTwoBookings($date);

        $this->actingAs($this->teacher1User)
            ->post('/attendance/private-bookings/'.$booking2->id.'/mark', ['status' => 'present'])
            ->assertForbidden();
    }

    public function test_admin_can_see_all_private_bookings(): void
    {
        $date = '2026-05-25';
        $this->seedTwoBookings($date);

        $response = $this->actingAs($this->admin)->get('/private-bookings?date='.$date);
        $response->assertOk();
        $response->assertSee('Teacher A');
        $response->assertSee('Teacher B');
    }

    public function test_front_desk_can_see_all_private_bookings(): void
    {
        $date = '2026-05-25';
        $this->seedTwoBookings($date);

        $response = $this->actingAs($this->frontDesk)->get('/private-bookings?date='.$date);
        $response->assertOk();
        $response->assertSee('Teacher A');
        $response->assertSee('Teacher B');
    }
}


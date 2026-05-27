<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use App\Services\DanceStudio\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegularClassAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Studio $studio;

    private User $admin;

    private Room $roomA;

    private Teacher $teacherA;

    private ClassType $regularClassType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create studio with business hours and booking interval
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

        $this->regularClassType = ClassType::query()->create([
            'studio_id' => $this->studio->id,
            'name' => 'Regular Class',
            'kind' => 'regular',
            'default_deduct_units' => 1,
            'is_active' => true,
        ]);
    }

    public function test_availability_page_shows_regular_class_status(): void
    {
        // 2026-05-18 is a Monday (ISO weekday = 1)
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Monday Regular',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/availability?date=2026-05-18&duration=90');

        $response->assertOk();
        $response->assertSee('regular');
    }

    public function test_availability_page_shows_regular_reason_text(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Monday Regular',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/availability?date=2026-05-18&duration=90');

        $response->assertOk();
        $response->assertSee('Regular Class');
    }

    public function test_weekly_schedule_page_shows_regular_class(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Monday Regular',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'is_active' => true,
        ]);

        // week_start=2026-05-18 is Monday
        $response = $this->actingAs($this->admin)
            ->get('/weekly-schedule?week_start=2026-05-18');

        $response->assertOk();
        $response->assertSee('Monday Regular');
    }

    public function test_availability_does_not_show_regular_class_on_wrong_day(): void
    {
        // Create a Monday class
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Monday Regular',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'is_active' => true,
        ]);

        // 2026-05-19 is Tuesday — Monday class should not show as regular
        $response = $this->actingAs($this->admin)
            ->get('/availability?date=2026-05-19&duration=90');

        $response->assertOk();
        // On Tuesday, no regular class exists so 'regular' status should not appear
        // The page should still load fine, just no "regular" in the time slot
        $content = $response->content();
        // Check that the Monday class title does NOT appear on Tuesday
        $this->assertStringNotContainsString('Monday Regular', $content);
    }

    public function test_inactive_regular_class_not_shown_on_availability(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Inactive Monday',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/availability?date=2026-05-18&duration=90');

        $response->assertOk();
        $response->assertDontSee('Inactive Monday');
    }

    public function test_regular_class_with_ends_on_past_not_shown(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Expired Regular',
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-04-30',
            'is_active' => true,
        ]);

        // 2026-05-18 is after ends_on
        $response = $this->actingAs($this->admin)
            ->get('/availability?date=2026-05-18&duration=90');

        $response->assertOk();
        $response->assertDontSee('Expired Regular');
    }

    public function test_availability_shows_regular_when_starts_on_and_ends_on_are_datetime(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Datetime Range Regular',
            'day_of_week' => 1,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
            'starts_on' => '2026-05-18 00:00:00',
            'ends_on' => '2026-05-20 00:00:00',
            'is_active' => true,
        ]);

        $result = app(AvailabilityService::class)->getDailyAvailability($this->studio->id, '2026-05-18', 90);
        $slot = collect($result)->firstWhere('time', '11:30-13:00');
        $this->assertIsArray($slot);

        $room = collect($slot['rooms'])->firstWhere('room_id', $this->roomA->id);
        $this->assertIsArray($room);
        $this->assertSame('regular', $room['status']);
        $this->assertSame('Regular Class', $room['reason']);
    }
}

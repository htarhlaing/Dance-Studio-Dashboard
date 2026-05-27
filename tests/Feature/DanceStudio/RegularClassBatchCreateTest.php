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

class RegularClassBatchCreateTest extends TestCase
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
            'name' => 'Studio B',
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
            'name' => 'K-pop',
            'kind' => 'regular',
            'default_deduct_units' => 1,
            'is_active' => true,
        ]);
    }

    public function test_store_creates_multiple_regular_classes_for_selected_weekdays(): void
    {
        $response = $this->actingAs($this->admin)->post('/regular-classes', [
            'weekdays' => [1, 2],
            'start_time' => '11:30',
            'end_time' => '13:00',
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'K-pop',
            'starts_on' => '2026-05-25',
            'ends_on' => '2026-06-16',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/regular-classes');
        $this->assertSame(2, RegularClass::query()->count());

        $this->assertDatabaseHas('regular_classes', [
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'day_of_week' => 1,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
        ]);
        $this->assertDatabaseHas('regular_classes', [
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'day_of_week' => 2,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
        ]);

        $availability = app(AvailabilityService::class)->getDailyAvailability($this->studio->id, '2026-05-25', 90);
        $slot = collect($availability)->firstWhere('time', '11:30-13:00');
        $room = collect($slot['rooms'])->firstWhere('room_id', $this->roomA->id);
        $this->assertSame('regular', $room['status']);

        $availability = app(AvailabilityService::class)->getDailyAvailability($this->studio->id, '2026-05-26', 90);
        $slot = collect($availability)->firstWhere('time', '11:30-13:00');
        $room = collect($slot['rooms'])->firstWhere('room_id', $this->roomA->id);
        $this->assertSame('regular', $room['status']);
    }

    public function test_store_rolls_back_when_any_selected_weekday_conflicts(): void
    {
        RegularClass::query()->create([
            'studio_id' => $this->studio->id,
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'Existing',
            'day_of_week' => 2,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
            'starts_on' => '2026-05-25',
            'ends_on' => '2026-06-16',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/regular-classes', [
            'weekdays' => [1, 2],
            'start_time' => '11:30',
            'end_time' => '13:00',
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacherA->id,
            'class_type_id' => $this->regularClassType->id,
            'title' => 'K-pop',
            'starts_on' => '2026-05-25',
            'ends_on' => '2026-06-16',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors(['weekdays']);
        $this->assertSame(1, RegularClass::query()->count());
        $this->assertDatabaseMissing('regular_classes', [
            'studio_id' => $this->studio->id,
            'day_of_week' => 1,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
            'title' => 'K-pop',
        ]);
    }
}


<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyScheduleRegularDateRangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_weekly_schedule_regular_date_range_query_uses_dates_for_datetime_starts_on_ends_on(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $studioId = (int) $admin->studio_id;

        $roomId = (int) Room::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->value('id');

        $teacherId = (int) Teacher::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->value('id');

        $classTypeId = (int) ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('kind', 'regular')->orWhere('name', 'Regular Class');
            })
            ->orderBy('id')
            ->value('id');

        RegularClass::query()->create([
            'studio_id' => $studioId,
            'room_id' => $roomId,
            'teacher_id' => $teacherId > 0 ? $teacherId : null,
            'class_type_id' => $classTypeId,
            'title' => 'K-pop Beginner',
            'day_of_week' => 1,
            'start_time' => '11:30:00',
            'end_time' => '13:00:00',
            'starts_on' => '2026-05-25 00:00:00',
            'ends_on' => '2026-05-27 00:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/weekly-schedule?week_start=2026-05-25');
        $response->assertOk();
        $response->assertSee('K-pop Beginner');

        $response = $this->actingAs($admin)->get('/weekly-schedule?week_start=2026-05-18');
        $response->assertOk();
        $response->assertDontSee('K-pop Beginner');
    }
}


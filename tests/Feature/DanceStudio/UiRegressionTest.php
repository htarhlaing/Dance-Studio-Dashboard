<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_regular_class_create_shows_title_required_error_message(): void
    {
        $frontDesk = User::query()->where('email', 'frontdesk@example.com')->firstOrFail();
        $studioId = (int) $frontDesk->studio_id;

        $roomId = (int) Room::query()->where('studio_id', $studioId)->orderBy('id')->value('id');
        $teacherId = (int) Teacher::query()->where('studio_id', $studioId)->orderBy('id')->value('id');
        $classTypeId = (int) ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('kind', 'regular')->orWhere('name', 'Regular Class');
            })
            ->orderBy('id')
            ->value('id');

        $this->actingAs($frontDesk)
            ->from('/regular-classes/create')
            ->post('/regular-classes', [
                'weekdays' => [1],
                'start_time' => '11:30',
                'end_time' => '13:00',
                'room_id' => $roomId,
                'teacher_id' => $teacherId,
                'class_type_id' => $classTypeId,
                'title' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/regular-classes/create');

        $response = $this->actingAs($frontDesk)->get('/regular-classes/create');
        $response->assertOk();
        $response->assertSee('The title field is required.');
    }

    public function test_availability_does_not_render_private_booked_raw_status_and_renders_booked_label(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $studioId = (int) $admin->studio_id;

        $roomId = (int) Room::query()->where('studio_id', $studioId)->orderBy('id')->value('id');
        $teacherId = (int) Teacher::query()->where('studio_id', $studioId)->orderBy('id')->value('id');
        $studentId = (int) \App\Models\DanceStudio\Student::query()->where('studio_id', $studioId)->orderBy('id')->value('id');
        $classTypeId = (int) ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('kind', 'private')->orWhere('name', 'Private Lesson');
            })
            ->orderBy('id')
            ->value('id');

        PrivateBooking::query()->create([
            'studio_id' => $studioId,
            'room_id' => $roomId,
            'teacher_id' => $teacherId,
            'student_id' => $studentId,
            'class_type_id' => $classTypeId,
            'start_at' => '2026-05-25 08:30:00',
            'end_at' => '2026-05-25 10:00:00',
            'status' => 'pending',
            'requested_by_user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/availability?date=2026-05-25&duration=90');
        $response->assertOk();
        $response->assertDontSee('>private_booked<', false);
        $response->assertSee('>Booked<', false);
    }
}


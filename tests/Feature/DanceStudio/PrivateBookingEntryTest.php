<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateBookingEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(CarbonImmutable::parse('2026-05-25 09:00:00'));
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_private_bookings_page_primary_entry_links_to_availability(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $studioId = (int) $admin->studio_id;

        $response = $this->actingAs($admin)->get('/private-bookings');
        $response->assertOk();

        $expected = url('/availability').'?date=2026-05-25&amp;duration=90';
        $response->assertSee('Find Available Slot');
        $response->assertSee('href="'.$expected.'"', false);
        $response->assertSee('Manual Create');
    }

    public function test_front_desk_can_see_find_available_slot_button(): void
    {
        $frontDesk = User::query()->where('email', 'frontdesk@example.com')->firstOrFail();

        $response = $this->actingAs($frontDesk)->get('/private-bookings');
        $response->assertOk();
        $response->assertSee('Find Available Slot');
    }

    public function test_teacher_cannot_see_create_entry_when_teacher_can_create_booking_is_false_and_create_page_is_forbidden(): void
    {
        $teacher = User::query()->where('email', 'teacher1@example.com')->firstOrFail();
        $studioId = (int) $teacher->studio_id;

        Studio::query()->whereKey($studioId)->update([
            'teacher_can_create_booking' => false,
        ]);

        $response = $this->actingAs($teacher)->get('/private-bookings');
        $response->assertOk();
        $response->assertDontSee('Find Available Slot');
        $response->assertDontSee('Manual Create');

        $this->actingAs($teacher)->get('/private-bookings/create')->assertForbidden();
    }

    public function test_availability_book_link_includes_date_room_time_and_duration_params(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $studioId = (int) $admin->studio_id;
        $roomId = (int) Room::query()->where('studio_id', $studioId)->orderBy('id')->value('id');

        $response = $this->actingAs($admin)->get('/availability?date=2026-05-25&duration=90');
        $response->assertOk();

        $response->assertSee(url('/private-bookings/create').'?date=2026-05-25', false);
        $response->assertSee('room_id='.$roomId, false);
        $response->assertSee('start_time=08%3A30', false);
        $response->assertSee('end_time=10%3A00', false);
        $response->assertSee('duration=90', false);
    }

    public function test_private_booking_create_prefills_fields_and_back_to_availability_uses_query_params(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $studioId = (int) $admin->studio_id;
        $roomId = (int) Room::query()->where('studio_id', $studioId)->orderBy('id')->value('id');

        $response = $this->actingAs($admin)->get('/private-bookings/create?date=2026-05-25&room_id='.$roomId.'&start_time=11:30&end_time=13:00&duration=90');
        $response->assertOk();

        $response->assertSee('name="date" value="2026-05-25"', false);
        $response->assertSee('name="room_id" value="'.$roomId.'"', false);
        $response->assertSee('name="start_time" value="11:30"', false);
        $response->assertSee('name="end_time" value="13:00"', false);
        $response->assertSee('name="duration" value="90"', false);

        $response->assertSee('href="'.url('/availability').'?date=2026-05-25&amp;duration=90"', false);
    }
}

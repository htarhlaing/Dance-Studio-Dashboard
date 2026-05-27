<?php

namespace Tests\Feature\DanceStudio;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioSettingsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_access_studio_settings_dashboard_and_sees_cards_and_links(): void
    {
        $this->actingAs(\App\Models\User::query()->where('email', 'admin@example.com')->firstOrFail())
            ->get('/studio-settings')
            ->assertOk()
            ->assertSee('Business Hours')
            ->assertSee('Booking Rules')
            ->assertSee('Rooms')
            ->assertSee('Class Types')
            ->assertSee('Package Types')
            ->assertSee('href="'.url('/rooms').'"', false)
            ->assertSee('href="'.url('/class-types').'"', false)
            ->assertSee('href="'.url('/package-types').'"', false)
            ->assertSee('href="'.url('/studio-settings/edit').'#business-hours"', false)
            ->assertSee('href="'.url('/studio-settings/edit').'#booking-rules"', false);
    }

    public function test_front_desk_cannot_access_studio_settings_dashboard(): void
    {
        $this->actingAs(\App\Models\User::query()->where('email', 'frontdesk@example.com')->firstOrFail())
            ->get('/studio-settings')
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_studio_settings_dashboard(): void
    {
        $this->actingAs(\App\Models\User::query()->where('email', 'teacher1@example.com')->firstOrFail())
            ->get('/studio-settings')
            ->assertForbidden();
    }

    public function test_admin_can_access_studio_settings_edit_page(): void
    {
        $this->actingAs(\App\Models\User::query()->where('email', 'admin@example.com')->firstOrFail())
            ->get('/studio-settings/edit')
            ->assertOk()
            ->assertSee('Booking Rules');
    }
}


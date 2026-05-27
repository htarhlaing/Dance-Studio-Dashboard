<?php

namespace Tests\Feature\DanceStudio;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function loginAs(string $email): void
    {
        $this->post('/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_guest_cannot_access_availability(): void
    {
        $this->get('/availability')->assertRedirect('/login');
    }

    public function test_admin_can_access_all_admin_routes(): void
    {
        $this->loginAs('admin@example.com');

        $urls = [
            '/dashboard',
            '/availability',
            '/weekly-schedule',
            '/regular-classes',
            '/private-bookings',
            '/attendance',
            '/students',
            '/teachers',
            '/rooms',
            '/student-packages',
            '/package-types',
            '/class-types',
            '/studio-settings',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_front_desk_can_access_front_desk_routes(): void
    {
        $this->loginAs('frontdesk@example.com');

        $urls = [
            '/dashboard',
            '/availability',
            '/weekly-schedule',
            '/regular-classes',
            '/private-bookings',
            '/attendance',
            '/students',
            '/teachers',
            '/rooms',
            '/student-packages',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_front_desk_cannot_access_admin_only_routes(): void
    {
        $this->loginAs('frontdesk@example.com');

        $urls = [
            '/package-types',
            '/class-types',
            '/studio-settings',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_teacher_can_access_teacher_routes(): void
    {
        $this->loginAs('teacher1@example.com');

        $urls = [
            '/dashboard',
            '/availability',
            '/private-bookings',
            '/attendance',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_teacher_cannot_access_management_or_admin_routes(): void
    {
        $this->loginAs('teacher1@example.com');

        $urls = [
            '/students',
            '/teachers',
            '/rooms',
            '/package-types',
            '/class-types',
            '/studio-settings',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_logout_then_dashboard_redirects_to_login(): void
    {
        $this->loginAs('admin@example.com');

        $this->post('/logout')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }
}


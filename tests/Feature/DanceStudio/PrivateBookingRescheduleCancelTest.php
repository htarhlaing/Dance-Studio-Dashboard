<?php

namespace Tests\Feature\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use App\Services\DanceStudio\BookingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrivateBookingRescheduleCancelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $frontDesk;
    private User $teacher1User;
    private User $teacher2User;
    private Teacher $teacher1;
    private Teacher $teacher2;
    private Room $roomA;
    private Student $student;
    private int $privateClassTypeId;
    private int $studioId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->frontDesk = User::query()->where('email', 'frontdesk@example.com')->firstOrFail();
        $this->teacher1User = User::query()->where('email', 'teacher1@example.com')->firstOrFail();
        $this->teacher2User = User::query()->where('email', 'teacher2@example.com')->firstOrFail();

        $this->studioId = (int) $this->admin->studio_id;

        $this->teacher1 = Teacher::query()->where('user_id', $this->teacher1User->id)->firstOrFail();
        $this->teacher2 = Teacher::query()->where('user_id', $this->teacher2User->id)->firstOrFail();

        $this->roomA = Room::query()->where('studio_id', $this->studioId)->orderBy('id')->firstOrFail();
        $this->student = Student::query()->where('studio_id', $this->studioId)->orderBy('id')->firstOrFail();

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

    private function createBooking(string $date, string $startTime, string $endTime, int $teacherId): PrivateBooking
    {
        return app(BookingService::class)->createPrivateBooking([
            'studio_id' => $this->studioId,
            'room_id' => $this->roomA->id,
            'teacher_id' => $teacherId,
            'student_id' => $this->student->id,
            'class_type_id' => $this->privateClassTypeId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'requested_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_cancel_pending_booking(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);

        $this->actingAs($this->admin)
            ->post('/private-bookings/'.$booking->id.'/cancel')
            ->assertRedirect('/private-bookings');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
    }

    public function test_cancel_releases_time_for_new_booking(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);

        $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/cancel');
        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);

        $newBooking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);
        $this->assertSame('pending', $newBooking->status);
    }

    public function test_completed_booking_cannot_be_cancelled(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);
        $booking->status = 'completed';
        $booking->save();

        $this->actingAs($this->admin)
            ->post('/private-bookings/'.$booking->id.'/cancel')
            ->assertSessionHasErrors(['booking']);

        $booking->refresh();
        $this->assertSame('completed', $booking->status);
    }

    public function test_cancelled_booking_cannot_be_marked_present(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);
        $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/cancel');
        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);

        $this->actingAs($this->admin)
            ->post('/attendance/private-bookings/'.$booking->id.'/mark', ['status' => 'present'])
            ->assertForbidden();
    }

    public function test_admin_can_reschedule_pending_booking_and_old_time_is_released(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);

        $this->actingAs($this->admin)
            ->post('/private-bookings/'.$booking->id.'/reschedule', [
                'date' => '2026-05-25',
                'start_time' => '12:00',
                'end_time' => '13:30',
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacher1->id,
            ])
            ->assertRedirect('/private-bookings');

        $booking->refresh();
        $this->assertSame('12:00', $booking->start_at?->format('H:i'));
        $this->assertSame('13:30', $booking->end_at?->format('H:i'));
        $this->assertSame('pending', $booking->status);

        $oldSlotBooking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);
        $this->assertSame('pending', $oldSlotBooking->status);
    }

    public function test_rescheduled_booking_blocks_new_time_slot(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);

        $this->actingAs($this->admin)->post('/private-bookings/'.$booking->id.'/reschedule', [
            'date' => '2026-05-25',
            'start_time' => '12:00',
            'end_time' => '13:30',
            'room_id' => $this->roomA->id,
            'teacher_id' => $this->teacher1->id,
        ]);

        try {
            $this->createBooking('2026-05-25', '12:00', '13:30', $this->teacher1->id);
            $this->fail('Expected ValidationException when booking a conflicting slot.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('booking', $e->errors());
        }
    }

    public function test_reschedule_to_conflicting_time_fails(): void
    {
        $bookingA = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);
        $this->createBooking('2026-05-25', '12:00', '13:30', $this->teacher1->id);

        $this->actingAs($this->admin)
            ->post('/private-bookings/'.$bookingA->id.'/reschedule', [
                'date' => '2026-05-25',
                'start_time' => '12:00',
                'end_time' => '13:30',
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacher1->id,
            ])
            ->assertSessionHasErrors(['booking']);

        $bookingA->refresh();
        $this->assertSame('10:00', $bookingA->start_at?->format('H:i'));
        $this->assertSame('11:30', $bookingA->end_at?->format('H:i'));
    }

    public function test_reschedule_does_not_conflict_with_itself(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher1->id);

        $this->actingAs($this->admin)
            ->post('/private-bookings/'.$booking->id.'/reschedule', [
                'date' => '2026-05-25',
                'start_time' => '10:00',
                'end_time' => '11:30',
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacher1->id,
            ])
            ->assertRedirect('/private-bookings');

        $booking->refresh();
        $this->assertSame('10:00', $booking->start_at?->format('H:i'));
        $this->assertSame('11:30', $booking->end_at?->format('H:i'));
    }

    public function test_teacher_cannot_cancel_or_reschedule_other_teachers_booking(): void
    {
        $booking = $this->createBooking('2026-05-25', '10:00', '11:30', $this->teacher2->id);

        $this->actingAs($this->teacher1User)
            ->post('/private-bookings/'.$booking->id.'/cancel')
            ->assertForbidden();

        $this->actingAs($this->teacher1User)
            ->post('/private-bookings/'.$booking->id.'/reschedule', [
                'date' => '2026-05-25',
                'start_time' => '12:00',
                'end_time' => '13:30',
                'room_id' => $this->roomA->id,
                'teacher_id' => $this->teacher2->id,
            ])
            ->assertForbidden();
    }
}


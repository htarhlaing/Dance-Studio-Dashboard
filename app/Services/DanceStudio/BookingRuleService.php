<?php

namespace App\Services\DanceStudio;

use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Studio;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingRuleService
{
    public function assertCanCreateBooking(User $user, Studio $studio, CarbonImmutable $startAt): void
    {
        $this->assertNotInPast($startAt, 'Cannot create a booking in the past.');

        if ($user->role === 'teacher') {
            if (! (bool) $studio->teacher_can_create_booking) {
                throw ValidationException::withMessages([
                    'booking' => 'Teachers are not allowed to create private bookings.',
                ]);
            }

            $this->assertMinimumNotice($startAt, $this->minimumBookingNoticeHours($studio), 'Teachers must book at least %d hours in advance.');

            return;
        }

        if (in_array($user->role, ['admin', 'front_desk'], true)) {
            if ((bool) $studio->allow_admin_frontdesk_override_notice) {
                return;
            }

            $this->assertMinimumNotice($startAt, $this->minimumBookingNoticeHours($studio), 'Bookings must be made at least %d hours in advance.');
        }
    }

    public function assertCanRescheduleBooking(User $user, Studio $studio, CarbonImmutable $newStartAt): void
    {
        $this->assertNotInPast($newStartAt, 'Cannot reschedule a booking to the past.');

        if ($user->role === 'teacher') {
            $this->assertMinimumNotice($newStartAt, $this->minimumRescheduleNoticeHours($studio), 'Teachers must reschedule at least %d hours in advance.');

            return;
        }

        if (in_array($user->role, ['admin', 'front_desk'], true)) {
            if ((bool) $studio->allow_admin_frontdesk_override_notice) {
                return;
            }

            $this->assertMinimumNotice($newStartAt, $this->minimumRescheduleNoticeHours($studio), 'Reschedules must be made at least %d hours in advance.');
        }
    }

    public function assertCanCancelBooking(User $user, Studio $studio, PrivateBooking $booking): void
    {
        $startAt = $booking->start_at instanceof \DateTimeInterface
            ? CarbonImmutable::parse($booking->start_at)
            : null;

        if ($startAt === null) {
            throw ValidationException::withMessages([
                'booking' => 'Invalid booking time.',
            ]);
        }

        if ($user->role === 'teacher') {
            $this->assertMinimumNotice($startAt, $this->minimumCancelNoticeHours($studio), 'Teachers must cancel at least %d hours in advance.');

            return;
        }

        if (in_array($user->role, ['admin', 'front_desk'], true)) {
            if ((bool) $studio->allow_admin_frontdesk_override_notice) {
                return;
            }

            $this->assertMinimumNotice($startAt, $this->minimumCancelNoticeHours($studio), 'Cancellations must be made at least %d hours in advance.');
        }
    }

    private function minimumBookingNoticeHours(Studio $studio): int
    {
        $hours = is_numeric($studio->minimum_booking_notice_hours) ? (int) $studio->minimum_booking_notice_hours : 48;

        return max(0, $hours);
    }

    private function minimumRescheduleNoticeHours(Studio $studio): int
    {
        $hours = is_numeric($studio->minimum_reschedule_notice_hours) ? (int) $studio->minimum_reschedule_notice_hours : 48;

        return max(0, $hours);
    }

    private function minimumCancelNoticeHours(Studio $studio): int
    {
        $hours = is_numeric($studio->minimum_cancel_notice_hours) ? (int) $studio->minimum_cancel_notice_hours : 24;

        return max(0, $hours);
    }

    private function assertNotInPast(CarbonImmutable $startAt, string $message): void
    {
        if ($startAt->lt(CarbonImmutable::now())) {
            throw ValidationException::withMessages([
                'booking' => $message,
            ]);
        }
    }

    private function assertMinimumNotice(CarbonImmutable $startAt, int $minimumHours, string $messageTemplate): void
    {
        if ($minimumHours <= 0) {
            return;
        }

        $minStartAt = CarbonImmutable::now()->addHours($minimumHours);
        if ($startAt->lt($minStartAt)) {
            throw ValidationException::withMessages([
                'booking' => sprintf($messageTemplate, $minimumHours),
            ]);
        }
    }
}


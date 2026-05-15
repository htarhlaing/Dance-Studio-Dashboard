<?php

namespace App\Services\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    public function resolveDurationMinutes(int $studioId, ?int $durationMinutes = null): int
    {
        if ($durationMinutes !== null && $durationMinutes > 0) {
            return $durationMinutes;
        }

        $resolved = ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->where('name', 'Private Lesson')
                    ->orWhere('kind', 'private');
            })
            ->orderByRaw("CASE WHEN name = 'Private Lesson' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->value('default_duration_minutes');

        $resolved = is_numeric($resolved) ? (int) $resolved : 0;

        return $resolved > 0 ? $resolved : 60;
    }

    public function resolveBusinessHours(int $studioId): array
    {
        $startTime = (string) Studio::query()->whereKey($studioId)->value('business_start_time');
        $endTime = (string) Studio::query()->whereKey($studioId)->value('business_end_time');

        $startTime = $startTime !== '' ? substr($startTime, 0, 5) : '10:00';
        $endTime = $endTime !== '' ? substr($endTime, 0, 5) : '22:00';

        return [$startTime, $endTime];
    }

    public function resolveBookingIntervalMinutes(int $studioId, int $durationMinutes): int
    {
        $interval = Studio::query()->whereKey($studioId)->value('booking_interval_minutes');
        $interval = is_numeric($interval) ? (int) $interval : 0;

        return $interval > 0 ? $interval : $durationMinutes;
    }

    public function getDailyAvailability(int $studioId, string $date, ?int $durationMinutes = null): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $isoWeekday = $day->isoWeekday();

        $slotDurationMinutes = $this->resolveDurationMinutes($studioId, $durationMinutes);
        [$businessStartTime, $businessEndTime] = $this->resolveBusinessHours($studioId);
        $slotStartStepMinutes = $this->resolveBookingIntervalMinutes($studioId, $slotDurationMinutes);

        $businessStartAt = $day->setTimeFromTimeString($businessStartTime);
        $businessEndAt = $day->setTimeFromTimeString($businessEndTime);

        $rooms = Room::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        $regularClassesByRoomId = RegularClass::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where('day_of_week', $isoWeekday)
            ->where(function ($query) use ($day) {
                $query
                    ->whereNull('starts_on')
                    ->orWhere('starts_on', '<=', $day->toDateString());
            })
            ->where(function ($query) use ($day) {
                $query
                    ->whereNull('ends_on')
                    ->orWhere('ends_on', '>=', $day->toDateString());
            })
            ->get(['room_id', 'start_time', 'end_time'])
            ->groupBy('room_id');

        $privateBookingsByRoomId = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->where('start_at', '<', $day->endOfDay())
            ->where('end_at', '>', $day->startOfDay())
            ->get(['room_id', 'start_at', 'end_at', 'status'])
            ->groupBy('room_id');

        $results = [];

        for ($slotStartAt = $businessStartAt; $slotStartAt->lt($businessEndAt); $slotStartAt = $slotStartAt->addMinutes($slotStartStepMinutes)) {
            $slotEndAt = $slotStartAt->addMinutes($slotDurationMinutes);
            if ($slotEndAt->gt($businessEndAt)) {
                break;
            }

            $slotLabel = $slotStartAt->format('H:i').'-'.$slotEndAt->format('H:i');

            $roomStatuses = [];

            foreach ($rooms as $room) {
                $status = 'available';
                $reason = null;

                $regulars = $regularClassesByRoomId->get($room->id, collect());
                foreach ($regulars as $regular) {
                    $regularStartAt = $day->setTimeFromTimeString($regular->start_time);
                    $regularEndAt = $day->setTimeFromTimeString($regular->end_time);
                    if ($regularStartAt->lt($slotEndAt) && $regularEndAt->gt($slotStartAt)) {
                        $status = 'regular';
                        $reason = 'Regular Class';
                        break;
                    }
                }

                if ($status === 'available') {
                    $bookings = $privateBookingsByRoomId->get($room->id, collect());
                    foreach ($bookings as $booking) {
                        $bookingStartAt = CarbonImmutable::parse($booking->start_at);
                        $bookingEndAt = CarbonImmutable::parse($booking->end_at);
                        if ($bookingStartAt->lt($slotEndAt) && $bookingEndAt->gt($slotStartAt)) {
                            $status = $booking->status === 'completed' ? 'completed' : 'private_booked';
                            $reason = $booking->status === 'completed' ? 'Completed' : 'Private Lesson';
                            break;
                        }
                    }
                }

                $roomStatuses[] = [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'status' => $status,
                    'reason' => $reason,
                ];
            }

            $results[] = [
                'time' => $slotLabel,
                'rooms' => $roomStatuses,
            ];
        }

        return $results;
    }
}

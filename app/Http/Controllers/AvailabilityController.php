<?php

namespace App\Http\Controllers;

use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use App\Services\DanceStudio\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request, AvailabilityService $availabilityService)
    {
        $date = $request->query('date');
        $date = is_string($date) && $date !== '' ? $date : CarbonImmutable::today()->toDateString();

        $studioId = (int) $request->query('studio_id', 0);
        if ($studioId <= 0) {
            $studioId = (int) Studio::query()->orderBy('id')->value('id');
        }

        $duration = $request->query('duration');
        $duration = is_numeric($duration) ? (int) $duration : null;
        $allowedDurations = [30, 60, 90, 120];
        $duration = $duration !== null && in_array($duration, $allowedDurations, true) ? $duration : null;

        $rooms = Room::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->get(['id', 'name']);

        $resolvedDurationMinutes = $studioId > 0 ? $availabilityService->resolveDurationMinutes($studioId, $duration) : 60;
        [$businessStartTime, $businessEndTime] = $studioId > 0 ? $availabilityService->resolveBusinessHours($studioId) : ['10:00', '22:00'];
        $bookingIntervalMinutes = $studioId > 0 ? $availabilityService->resolveBookingIntervalMinutes($studioId, $resolvedDurationMinutes) : $resolvedDurationMinutes;

        $availability = $studioId > 0
            ? $availabilityService->getDailyAvailability($studioId, $date, $duration)
            : [];

        return view('availability', [
            'date' => $date,
            'studioId' => $studioId,
            'duration' => $duration,
            'resolvedDurationMinutes' => $resolvedDurationMinutes,
            'businessStartTime' => $businessStartTime,
            'businessEndTime' => $businessEndTime,
            'bookingIntervalMinutes' => $bookingIntervalMinutes,
            'rooms' => $rooms,
            'availability' => $availability,
        ]);
    }
}

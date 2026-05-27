<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Services\DanceStudio\CurrentStudioResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class WeeklyScheduleController extends Controller
{
    public function __invoke(Request $request)
    {
        $studio = app(CurrentStudioResolver::class)->studio();
        $studioId = app(CurrentStudioResolver::class)->id();

        $roomId = $request->query('room_id');
        $roomId = is_numeric($roomId) ? (int) $roomId : 0;
        $roomId = $roomId > 0 ? $roomId : null;

        $weekStart = $request->query('week_start');
        $weekStart = is_string($weekStart) ? trim($weekStart) : '';
        $weekStart = preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart) === 1
            ? CarbonImmutable::parse($weekStart)->startOfDay()
            : CarbonImmutable::today()->startOfWeek(CarbonImmutable::MONDAY);

        $weekStart = $weekStart->startOfWeek(CarbonImmutable::MONDAY);
        $weekEnd = $weekStart->addDays(6)->endOfDay();

        $businessStart = $studio?->business_start_time ? substr((string) $studio->business_start_time, 0, 5) : null;
        $businessEnd = $studio?->business_end_time ? substr((string) $studio->business_end_time, 0, 5) : null;
        $intervalMinutes = (int) ($studio?->booking_interval_minutes ?? 0);

        $businessStart = $businessStart && preg_match('/^\d{2}:\d{2}$/', $businessStart) === 1 ? $businessStart : '10:00';
        $businessEnd = $businessEnd && preg_match('/^\d{2}:\d{2}$/', $businessEnd) === 1 ? $businessEnd : '22:00';
        $intervalMinutes = $intervalMinutes > 0 ? $intervalMinutes : 60;

        $rooms = Room::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($roomId !== null && ! $rooms->contains('id', $roomId)) {
            $roomId = null;
        }

        $regularClasses = RegularClass::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->when($roomId !== null, fn ($q) => $q->where('room_id', $roomId))
            ->where(function ($query) use ($weekEnd) {
                $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', $weekEnd->toDateString());
            })
            ->where(function ($query) use ($weekStart) {
                $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $weekStart->toDateString());
            })
            ->with(['room', 'teacher', 'classType'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $privateBookings = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->when($roomId !== null, fn ($q) => $q->where('room_id', $roomId))
            ->where('start_at', '<', $weekEnd)
            ->where('end_at', '>', $weekStart)
            ->with(['room', 'teacher', 'student'])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->addDays($i)->startOfDay();
            $days[] = [
                'date' => $date,
                'label' => $date->format('D'),
                'iso_weekday' => $date->isoWeekday(),
            ];
        }

        $slots = $this->buildSlots($businessStart, $businessEnd, $intervalMinutes);

        $regularByIsoWeekday = [];
        foreach ($regularClasses as $regularClass) {
            $iso = $this->normalizeDayOfWeekToIso($regularClass->day_of_week);
            if ($iso === null) {
                continue;
            }
            $regularByIsoWeekday[$iso] = $regularByIsoWeekday[$iso] ?? [];
            $regularByIsoWeekday[$iso][] = $regularClass;
        }

        $bookingsByDate = [];
        foreach ($privateBookings as $booking) {
            $startDate = $booking->start_at->copy()->startOfDay();
            $endDate = $booking->end_at->copy()->subSecond()->startOfDay();

            $cursor = $startDate->lessThan($weekStart) ? $weekStart->startOfDay() : CarbonImmutable::instance($startDate);
            $last = $endDate->greaterThan($weekEnd) ? $weekEnd->startOfDay() : CarbonImmutable::instance($endDate);

            while ($cursor->lessThanOrEqualTo($last)) {
                $key = $cursor->toDateString();
                $bookingsByDate[$key] = $bookingsByDate[$key] ?? [];
                $bookingsByDate[$key][] = $booking;
                $cursor = $cursor->addDay();
            }
        }

        $grid = [];
        foreach ($slots as $slotIndex => $slot) {
            $grid[$slotIndex] = [];
            foreach ($days as $dayIndex => $day) {
                $date = $day['date'];
                $slotStart = CarbonImmutable::parse($date->toDateString().' '.$slot['start'].':00');
                $slotEnd = CarbonImmutable::parse($date->toDateString().' '.$slot['end'].':00');

                $itemsByRoomId = [];

                $regularList = $regularByIsoWeekday[$day['iso_weekday']] ?? [];
                foreach ($regularList as $regular) {
                    $startsOn = $regular->starts_on?->toDateString();
                    if ($startsOn !== null && $date->toDateString() < $startsOn) {
                        continue;
                    }
                    $endsOn = $regular->ends_on?->toDateString();
                    if ($endsOn !== null && $date->toDateString() > $endsOn) {
                        continue;
                    }

                    $regularStart = CarbonImmutable::parse($date->toDateString().' '.substr((string) $regular->start_time, 0, 5).':00');
                    $regularEnd = CarbonImmutable::parse($date->toDateString().' '.substr((string) $regular->end_time, 0, 5).':00');

                    if (! ($slotStart->lt($regularEnd) && $slotEnd->gt($regularStart))) {
                        continue;
                    }

                    $itemsByRoomId[(int) $regular->room_id] = $itemsByRoomId[(int) $regular->room_id] ?? [];
                    $startTime = substr((string) $regular->start_time, 0, 5);
                    $endTime = substr((string) $regular->end_time, 0, 5);
                    $classTypeName = $regular->classType?->name;
                    $titleBase = trim((string) ($regular->title ?? ''));
                    $titlePrefix = $classTypeName !== null && $classTypeName !== '' ? $classTypeName : '';
                    $title = trim($titlePrefix.' '.$titleBase);
                    if ($title === '') {
                        $title = $classTypeName !== null && $classTypeName !== '' ? $classTypeName : 'Regular Class';
                    }
                    $itemsByRoomId[(int) $regular->room_id][] = [
                        'type' => 'regular',
                        'badge' => 'Regular',
                        'title' => $title,
                        'room_id' => (int) $regular->room_id,
                        'room_name' => $regular->room?->name,
                        'teacher_name' => $regular->teacher?->display_name,
                        'student_name' => null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => null,
                    ];
                }

                $bookingList = $bookingsByDate[$date->toDateString()] ?? [];
                foreach ($bookingList as $booking) {
                    if (! ($slotStart->lt($booking->end_at) && $slotEnd->gt($booking->start_at))) {
                        continue;
                    }

                    $itemsByRoomId[(int) $booking->room_id] = $itemsByRoomId[(int) $booking->room_id] ?? [];
                    $studentName = $booking->student?->name;
                    $studentLabel = $studentName !== null && $studentName !== '' ? $studentName : '-';
                    $itemsByRoomId[(int) $booking->room_id][] = [
                        'type' => 'private',
                        'badge' => 'Private',
                        'title' => 'Student: '.$studentLabel,
                        'room_id' => (int) $booking->room_id,
                        'room_name' => $booking->room?->name,
                        'teacher_name' => $booking->teacher?->display_name,
                        'student_name' => $studentName,
                        'start_time' => $booking->start_at?->format('H:i'),
                        'end_time' => $booking->end_at?->format('H:i'),
                        'status' => $booking->status,
                    ];
                }

                if ($roomId !== null) {
                    $items = $itemsByRoomId[$roomId] ?? [];
                } else {
                    $roomNameById = [];
                    foreach ($rooms as $r) {
                        $roomNameById[(int) $r->id] = (string) $r->name;
                    }

                    $items = [];
                    $roomIds = array_keys($itemsByRoomId);
                    usort($roomIds, function (int $a, int $b) use ($roomNameById) {
                        return strcmp($roomNameById[$a] ?? 'zzz', $roomNameById[$b] ?? 'zzz');
                    });

                    foreach ($roomIds as $rid) {
                        foreach ($itemsByRoomId[$rid] as $ri) {
                            $items[] = $ri;
                        }
                    }
                }

                $grid[$slotIndex][$dayIndex] = $items;
            }
        }

        return view('dance-studio.weekly-schedule.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => $days,
            'slots' => $slots,
            'grid' => $grid,
            'rooms' => $rooms,
            'roomId' => $roomId,
        ]);
    }

    private function buildSlots(string $businessStart, string $businessEnd, int $intervalMinutes): array
    {
        $start = CarbonImmutable::parse('2000-01-01 '.$businessStart.':00');
        $end = CarbonImmutable::parse('2000-01-01 '.$businessEnd.':00');

        $slots = [];
        $cursor = $start;
        while ($cursor->addMinutes($intervalMinutes)->lessThanOrEqualTo($end)) {
            $slotEnd = $cursor->addMinutes($intervalMinutes);
            $slots[] = [
                'start' => $cursor->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'label' => $cursor->format('H:i').'-'.$slotEnd->format('H:i'),
            ];
            $cursor = $slotEnd;
        }

        return $slots;
    }

    private function normalizeDayOfWeekToIso(mixed $value): ?int
    {
        if (is_numeric($value)) {
            $n = (int) $value;
            if ($n === 0) {
                return 7;
            }
            return $n >= 1 && $n <= 7 ? $n : null;
        }

        if (is_string($value)) {
            $v = strtolower(trim($value));
            $map = [
                'monday' => 1,
                'mon' => 1,
                'tuesday' => 2,
                'tue' => 2,
                'wednesday' => 3,
                'wed' => 3,
                'thursday' => 4,
                'thu' => 4,
                'friday' => 5,
                'fri' => 5,
                'saturday' => 6,
                'sat' => 6,
                'sunday' => 7,
                'sun' => 7,
            ];

            return $map[$v] ?? null;
        }

        return null;
    }
}

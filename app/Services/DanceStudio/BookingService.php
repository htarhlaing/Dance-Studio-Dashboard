<?php

namespace App\Services\DanceStudio;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\Teacher;
use App\Models\User as AppUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createPrivateBooking(array $data): PrivateBooking
    {
        $studioId = (int) ($data['studio_id'] ?? 0);
        $roomId = (int) ($data['room_id'] ?? 0);
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $studentId = (int) ($data['student_id'] ?? 0);
        $classTypeId = (int) ($data['class_type_id'] ?? 0);
        $date = (string) ($data['date'] ?? '');
        $startTime = (string) ($data['start_time'] ?? '');
        $endTime = (string) ($data['end_time'] ?? '');
        $requestedByUserId = (int) ($data['requested_by_user_id'] ?? 0);

        if ($studioId <= 0 || $roomId <= 0 || $teacherId <= 0 || $studentId <= 0 || $classTypeId <= 0 || $date === '' || $startTime === '' || $endTime === '') {
            throw ValidationException::withMessages([
                'booking' => 'Missing required fields for creating a private booking.',
            ]);
        }

        try {
            $day = CarbonImmutable::parse($date)->startOfDay();
            $newStartAt = $day->setTimeFromTimeString($startTime);
            $newEndAt = $day->setTimeFromTimeString($endTime);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'booking' => 'Invalid date or time format.',
            ]);
        }

        if ($newEndAt->lte($newStartAt)) {
            throw ValidationException::withMessages([
                'booking' => 'End time must be after start time.',
            ]);
        }

        $roomStudioId = Room::query()->whereKey($roomId)->value('studio_id');
        if ((int) $roomStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'booking' => 'Room does not belong to this studio.',
            ]);
        }

        $teacherStudioId = Teacher::query()->whereKey($teacherId)->value('studio_id');
        if ((int) $teacherStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'booking' => 'Teacher does not belong to this studio.',
            ]);
        }

        $studentStudioId = Student::query()->whereKey($studentId)->value('studio_id');
        if ((int) $studentStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'booking' => 'Student does not belong to this studio.',
            ]);
        }

        $classTypeStudioId = ClassType::query()->whereKey($classTypeId)->value('studio_id');
        if ((int) $classTypeStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'booking' => 'Class type does not belong to this studio.',
            ]);
        }

        return DB::transaction(function () use ($studioId, $roomId, $teacherId, $studentId, $classTypeId, $requestedByUserId, $day, $newStartAt, $newEndAt) {
            $isoWeekday = $day->isoWeekday();

            $regularClasses = RegularClass::query()
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
                ->get(['room_id', 'teacher_id', 'start_time', 'end_time']);

            foreach ($regularClasses as $regularClass) {
                $regularStartAt = $day->setTimeFromTimeString($regularClass->start_time);
                $regularEndAt = $day->setTimeFromTimeString($regularClass->end_time);

                if (! ($newStartAt->lt($regularEndAt) && $newEndAt->gt($regularStartAt))) {
                    continue;
                }

                if ((int) $regularClass->room_id === $roomId) {
                    throw ValidationException::withMessages([
                        'booking' => 'This room is already occupied by a regular class.',
                    ]);
                }

                if ($regularClass->teacher_id !== null && (int) $regularClass->teacher_id === $teacherId) {
                    throw ValidationException::withMessages([
                        'booking' => 'This teacher already has a class at this time.',
                    ]);
                }
            }

            $conflictingBookings = PrivateBooking::query()
                ->where('studio_id', $studioId)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->where('start_at', '<', $newEndAt)
                ->where('end_at', '>', $newStartAt)
                ->lockForUpdate()
                ->get(['room_id', 'teacher_id']);

            foreach ($conflictingBookings as $booking) {
                if ((int) $booking->room_id === $roomId) {
                    throw ValidationException::withMessages([
                        'booking' => 'This room is already booked for a private lesson.',
                    ]);
                }

                if ((int) $booking->teacher_id === $teacherId) {
                    throw ValidationException::withMessages([
                        'booking' => 'This teacher already has a class at this time.',
                    ]);
                }
            }

            $resolvedRequestedByUserId = $requestedByUserId;
            if ($resolvedRequestedByUserId <= 0) {
                $resolvedRequestedByUserId = (int) Auth::id();
            }
            if ($resolvedRequestedByUserId <= 0) {
                $resolvedRequestedByUserId = (int) AppUser::query()
                    ->where('studio_id', $studioId)
                    ->orderBy('id')
                    ->value('id');
            }
            if ($resolvedRequestedByUserId <= 0) {
                throw ValidationException::withMessages([
                    'booking' => 'requested_by_user_id is required.',
                ]);
            }

            return PrivateBooking::query()->create([
                'studio_id' => $studioId,
                'room_id' => $roomId,
                'teacher_id' => $teacherId,
                'student_id' => $studentId,
                'class_type_id' => $classTypeId,
                'start_at' => $newStartAt,
                'end_at' => $newEndAt,
                'status' => 'pending',
                'requested_by_user_id' => $resolvedRequestedByUserId,
            ]);
        });
    }
}

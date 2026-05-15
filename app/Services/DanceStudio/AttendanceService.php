<?php

namespace App\Services\DanceStudio;

use App\Models\DanceStudio\AttendanceRecord;
use App\Models\DanceStudio\PackageTransaction;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\StudentPackage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function markPrivateBookingAttendance(
        int $privateBookingId,
        string $status,
        ?int $markedByUserId = null,
        ?string $notes = null
    ): AttendanceRecord {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['present', 'absent', 'leave', 'cancelled'];

        if (! in_array($normalizedStatus, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'attendance' => 'Invalid attendance status.',
            ]);
        }

        return DB::transaction(function () use ($privateBookingId, $normalizedStatus, $markedByUserId, $notes) {
            $privateBooking = PrivateBooking::query()
                ->whereKey($privateBookingId)
                ->lockForUpdate()
                ->first();

            if ($privateBooking === null) {
                throw ValidationException::withMessages([
                    'attendance' => 'Private booking not found.',
                ]);
            }

            $existingAttendance = AttendanceRecord::query()
                ->where('private_booking_id', $privateBookingId)
                ->lockForUpdate()
                ->first();

            if ($existingAttendance !== null) {
                throw ValidationException::withMessages([
                    'attendance' => 'This private booking has already been checked in.',
                ]);
            }

            $resolvedMarkedByUserId = $markedByUserId ?? (int) Auth::id();
            if ($resolvedMarkedByUserId <= 0) {
                $resolvedMarkedByUserId = (int) $privateBooking->requested_by_user_id;
            }

            if ($resolvedMarkedByUserId <= 0) {
                throw ValidationException::withMessages([
                    'attendance' => 'marked_by_user_id is required.',
                ]);
            }

            $checkedInAt = CarbonImmutable::now();

            $attendanceRecord = AttendanceRecord::query()->create([
                'studio_id' => $privateBooking->studio_id,
                'private_booking_id' => $privateBooking->id,
                'student_id' => $privateBooking->student_id,
                'teacher_id' => $privateBooking->teacher_id,
                'room_id' => $privateBooking->room_id,
                'status' => $normalizedStatus,
                'checked_in_at' => $checkedInAt,
                'checked_in_by_user_id' => $resolvedMarkedByUserId,
                'notes' => $notes,
            ]);

            if ($normalizedStatus === 'cancelled') {
                $privateBooking->status = 'cancelled';
                $privateBooking->cancelled_at = $checkedInAt;
                $privateBooking->cancelled_by_user_id = $resolvedMarkedByUserId;
                $privateBooking->save();

                return $attendanceRecord;
            }

            if ($normalizedStatus !== 'present') {
                $privateBooking->status = 'completed';
                $privateBooking->save();

                return $attendanceRecord;
            }

            $studentPackage = StudentPackage::query()
                ->where('studio_id', $privateBooking->studio_id)
                ->where('student_id', $privateBooking->student_id)
                ->where('status', 'active')
                ->where('remaining_units', '>', 0)
                ->where(function ($query) use ($checkedInAt) {
                    $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>', $checkedInAt);
                })
                ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expires_at')
                ->orderBy('purchased_at')
                ->lockForUpdate()
                ->first();

            if ($studentPackage === null) {
                throw ValidationException::withMessages([
                    'attendance' => 'Student does not have an available lesson package.',
                ]);
            }

            $studentPackage->remaining_units = max(0, (int) $studentPackage->remaining_units - 1);
            $studentPackage->save();

            $existingDeduction = PackageTransaction::query()
                ->where('attendance_record_id', $attendanceRecord->id)
                ->lockForUpdate()
                ->first();

            if ($existingDeduction !== null) {
                throw ValidationException::withMessages([
                    'attendance' => 'This attendance has already been deducted.',
                ]);
            }

            PackageTransaction::query()->create([
                'studio_id' => $privateBooking->studio_id,
                'student_id' => $privateBooking->student_id,
                'student_package_id' => $studentPackage->id,
                'type' => 'deduction',
                'units_delta' => -1,
                'balance_after' => (int) $studentPackage->remaining_units,
                'occurred_at' => $checkedInAt,
                'attendance_record_id' => $attendanceRecord->id,
                'created_by_user_id' => $resolvedMarkedByUserId,
                'notes' => $notes,
            ]);

            $privateBooking->status = 'completed';
            $privateBooking->save();

            return $attendanceRecord;
        });
    }
}

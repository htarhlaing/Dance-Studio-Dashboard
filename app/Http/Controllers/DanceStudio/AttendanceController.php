<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PrivateBooking;
use App\Services\DanceStudio\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $studioId = 1;

        $date = $request->query('date');
        $date = is_string($date) && $date !== '' ? $date : CarbonImmutable::today()->toDateString();

        $bookings = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereDate('start_at', $date)
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at')
            ->get();

        return view('dance-studio.attendance.index', [
            'date' => $date,
            'bookings' => $bookings,
        ]);
    }

    public function mark(Request $request, PrivateBooking $privateBooking, AttendanceService $attendanceService)
    {
        $status = (string) $request->input('status', '');

        try {
            $attendanceService->markPrivateBookingAttendance($privateBooking->id, $status);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Attendance marked successfully.');
    }
}

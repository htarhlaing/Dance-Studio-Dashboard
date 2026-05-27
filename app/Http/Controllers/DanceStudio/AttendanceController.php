<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Teacher;
use App\Services\DanceStudio\CurrentStudioResolver;
use App\Services\DanceStudio\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        $role = (string) (auth()->user()?->role ?? '');

        $date = $request->query('date');
        $date = is_string($date) && $date !== '' ? $date : CarbonImmutable::today()->toDateString();

        $query = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->whereDate('start_at', $date)
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at');

        if ($role === 'teacher') {
            $teacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($teacherId), 403);

            $query->where('teacher_id', (int) $teacherId);
        }

        $bookings = $query->get();

        return view('dance-studio.attendance.index', [
            'date' => $date,
            'bookings' => $bookings,
        ]);
    }

    public function mark(Request $request, PrivateBooking $privateBooking, AttendanceService $attendanceService)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $privateBooking->studio_id === $studioId, 404);

        abort_if($privateBooking->status === 'cancelled', 403);

        $role = (string) (auth()->user()?->role ?? '');
        if ($role === 'teacher') {
            $teacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($teacherId), 403);
            abort_if((int) $privateBooking->teacher_id !== (int) $teacherId, 403);
        }

        $status = (string) $request->input('status', '');

        try {
            $attendanceService->markPrivateBookingAttendance($privateBooking->id, $status);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Attendance marked successfully.');
    }
}

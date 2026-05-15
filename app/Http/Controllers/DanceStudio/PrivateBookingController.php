<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\Teacher;
use App\Services\DanceStudio\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PrivateBookingController extends Controller
{
    public function index(Request $request)
    {
        $studioId = 1;

        $date = $request->query('date');
        $date = is_string($date) && $date !== '' ? $date : null;

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;

        $query = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at', 'desc');

        if ($date !== null) {
            $query->whereDate('start_at', $date);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        return view('dance-studio.private-bookings.index', [
            'bookings' => $bookings,
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $studioId = 1;

        $date = $request->query('date');
        $date = is_string($date) ? $date : '';

        $roomId = $request->query('room_id');
        $roomId = is_numeric($roomId) ? (int) $roomId : null;

        $startTime = $request->query('start_time');
        $startTime = is_string($startTime) ? $startTime : '';

        $endTime = $request->query('end_time');
        $endTime = is_string($endTime) ? $endTime : '';

        $duration = $request->query('duration');
        $duration = is_numeric($duration) ? (int) $duration : null;

        $room = $roomId !== null
            ? Room::query()->where('studio_id', $studioId)->whereKey($roomId)->first(['id', 'name'])
            : null;

        $students = Student::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        $teachers = Teacher::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'display_name', 'user_id']);

        $classTypeId = (int) ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where('kind', 'private')
            ->orderBy('id')
            ->value('id');

        if ($classTypeId <= 0) {
            $classTypeId = (int) ClassType::query()
                ->where('studio_id', $studioId)
                ->where('is_active', true)
                ->where('name', 'Private Lesson')
                ->orderBy('id')
                ->value('id');
        }

        return view('dance-studio.private-bookings.create', [
            'studioId' => $studioId,
            'date' => $date,
            'room' => $room,
            'roomId' => $roomId,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'duration' => $duration,
            'students' => $students,
            'teachers' => $teachers,
            'classTypeId' => $classTypeId,
        ]);
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $studioId = 1;

        $payload = [
            'studio_id' => $studioId,
            'room_id' => (int) $request->input('room_id'),
            'teacher_id' => (int) $request->input('teacher_id'),
            'student_id' => (int) $request->input('student_id'),
            'class_type_id' => (int) $request->input('class_type_id'),
            'date' => (string) $request->input('date'),
            'start_time' => (string) $request->input('start_time'),
            'end_time' => (string) $request->input('end_time'),
        ];

        $duration = $request->input('duration');
        $duration = is_numeric($duration) ? (int) $duration : null;

        try {
            $bookingService->createPrivateBooking($payload);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $query = array_filter([
            'date' => $payload['date'] !== '' ? $payload['date'] : null,
            'duration' => $duration,
        ], fn ($v) => $v !== null);

        return redirect()
            ->to(url('/availability').'?'.http_build_query($query))
            ->with('success', 'Private booking created successfully.');
    }
}

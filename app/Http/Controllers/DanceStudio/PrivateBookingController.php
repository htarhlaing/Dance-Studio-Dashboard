<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PrivateBooking;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Services\DanceStudio\AvailabilityService;
use App\Services\DanceStudio\BookingService;
use App\Services\DanceStudio\BookingRuleService;
use App\Services\DanceStudio\CurrentStudioResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PrivateBookingController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        $role = (string) (auth()->user()?->role ?? '');
        $studio = Studio::query()->findOrFail($studioId);

        $date = $request->query('date');
        $date = is_string($date) && $date !== '' ? $date : null;

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;

        $query = PrivateBooking::query()
            ->where('studio_id', $studioId)
            ->with(['room', 'student', 'teacher'])
            ->orderBy('start_at', 'desc');

        if ($role === 'teacher') {
            $teacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($teacherId), 403);

            $query->where('teacher_id', (int) $teacherId);
        }

        if ($date !== null) {
            $query->whereDate('start_at', $date);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        $today = CarbonImmutable::today()->toDateString();
        $resolvedDurationMinutes = app(AvailabilityService::class)->resolveDurationMinutes($studioId, null);
        $resolvedDurationMinutes = in_array($resolvedDurationMinutes, [30, 60, 90, 120], true) ? $resolvedDurationMinutes : 60;
        $findAvailabilityUrl = url('/availability').'?'.http_build_query([
            'date' => $today,
            'duration' => $resolvedDurationMinutes,
        ]);

        $canSeeCreateEntry = ! ($role === 'teacher' && ! (bool) $studio->teacher_can_create_booking);
        $showManualCreate = in_array($role, ['admin', 'front_desk'], true);

        return view('dance-studio.private-bookings.index', [
            'bookings' => $bookings,
            'date' => $date,
            'status' => $status,
            'findAvailabilityUrl' => $findAvailabilityUrl,
            'canSeeCreateEntry' => $canSeeCreateEntry,
            'showManualCreate' => $showManualCreate,
        ]);
    }

    public function create(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        $role = (string) (auth()->user()?->role ?? '');
        $studio = Studio::query()->findOrFail($studioId);
        abort_if($role === 'teacher' && ! (bool) $studio->teacher_can_create_booking, 403);

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

        $bookingDate = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $bookingDate = $date;
        }

        $eligibleStudentIds = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->where('remaining_units', '>', 0)
            ->where('status', 'active')
            ->when($bookingDate !== null, function ($query) use ($bookingDate) {
                $query->where(function ($q) use ($bookingDate) {
                    $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', $bookingDate);
                });
            })
            ->distinct()
            ->orderBy('student_id')
            ->pluck('student_id')
            ->all();

        $students = Student::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->whereIn('id', $eligibleStudentIds)
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

        $backQuery = array_filter([
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null,
            'duration' => is_int($duration) ? $duration : null,
        ], fn ($v) => $v !== null);
        $backToAvailabilityUrl = url('/availability').(count($backQuery) ? ('?'.http_build_query($backQuery)) : '');

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
            'role' => $role,
            'backToAvailabilityUrl' => $backToAvailabilityUrl,
        ]);
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        $role = (string) (auth()->user()?->role ?? '');

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

        try {
            $day = CarbonImmutable::parse($payload['date'])->startOfDay();
            $startAt = $day->setTimeFromTimeString((string) $payload['start_time']);
        } catch (\Throwable) {
            return back()->withErrors([
                'booking' => 'Invalid date or time format.',
            ])->withInput();
        }

        $studio = Studio::query()->findOrFail($studioId);
        try {
            app(BookingRuleService::class)->assertCanCreateBooking(auth()->user(), $studio, $startAt);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

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

    public function rescheduleForm(PrivateBooking $privateBooking)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $privateBooking->studio_id === $studioId, 404);

        $role = (string) (auth()->user()?->role ?? '');
        $resolvedTeacherId = null;
        if ($role === 'teacher') {
            $resolvedTeacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($resolvedTeacherId), 403);
            abort_if((int) $privateBooking->teacher_id !== (int) $resolvedTeacherId, 403);
        }

        abort_if(! in_array($privateBooking->status, ['pending', 'confirmed'], true), 403);

        $rooms = Room::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        $teachers = Teacher::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'display_name', 'user_id']);

        return view('dance-studio.private-bookings.reschedule', [
            'booking' => $privateBooking->loadMissing(['room', 'student', 'teacher']),
            'rooms' => $rooms,
            'teachers' => $teachers,
            'role' => $role,
            'resolvedTeacherId' => is_numeric($resolvedTeacherId) ? (int) $resolvedTeacherId : null,
        ]);
    }

    public function reschedule(Request $request, PrivateBooking $privateBooking, BookingService $bookingService)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $privateBooking->studio_id === $studioId, 404);

        $role = (string) (auth()->user()?->role ?? '');
        if ($role === 'teacher') {
            $resolvedTeacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($resolvedTeacherId), 403);
            abort_if((int) $privateBooking->teacher_id !== (int) $resolvedTeacherId, 403);
        }

        abort_if(! in_array($privateBooking->status, ['pending', 'confirmed'], true), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'room_id' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['required', 'integer', 'min:1'],
        ]);

        if ($role === 'teacher') {
            $data['teacher_id'] = (int) $privateBooking->teacher_id;
            $data['room_id'] = (int) $privateBooking->room_id;
        }

        try {
            $day = CarbonImmutable::parse((string) $data['date'])->startOfDay();
            $startAt = $day->setTimeFromTimeString((string) $data['start_time']);
        } catch (\Throwable) {
            return back()->withErrors([
                'booking' => 'Invalid date or time format.',
            ])->withInput();
        }

        $studio = Studio::query()->findOrFail($studioId);
        try {
            app(BookingRuleService::class)->assertCanRescheduleBooking(auth()->user(), $studio, $startAt);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            $bookingService->reschedulePrivateBooking($privateBooking, $data);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->to(url('/private-bookings'))
            ->with('success', 'Booking rescheduled successfully.');
    }

    public function cancel(PrivateBooking $privateBooking, BookingService $bookingService)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $privateBooking->studio_id === $studioId, 404);

        $role = (string) (auth()->user()?->role ?? '');
        if ($role === 'teacher') {
            $resolvedTeacherId = Teacher::query()
                ->where('studio_id', $studioId)
                ->where('user_id', (int) auth()->id())
                ->value('id');

            abort_if(! is_numeric($resolvedTeacherId), 403);
            abort_if((int) $privateBooking->teacher_id !== (int) $resolvedTeacherId, 403);
        }

        $studio = Studio::query()->findOrFail($studioId);
        try {
            app(BookingRuleService::class)->assertCanCancelBooking(auth()->user(), $studio, $privateBooking);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        try {
            $bookingService->cancelPrivateBooking($privateBooking, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->to(url('/private-bookings'))
            ->with('success', 'Booking cancelled successfully.');
    }
}

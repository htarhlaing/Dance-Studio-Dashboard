<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Teacher;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegularClassController extends Controller
{
    public function index()
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $regularClasses = RegularClass::query()
            ->where('studio_id', $studioId)
            ->with(['room', 'teacher', 'classType'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        return view('dance-studio.regular-classes.index', [
            'regularClasses' => $regularClasses,
        ]);
    }

    public function create()
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        return view('dance-studio.regular-classes.create', [
            'rooms' => $this->rooms($studioId),
            'teachers' => $this->teachers($studioId),
            'classTypes' => $this->classTypes($studioId),
        ]);
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $validated = $this->validatedStore($request);
        $weekdays = $validated['weekdays'];
        unset($validated['weekdays']);

        $roomId = (int) $validated['room_id'];
        $roomName = (string) Room::query()
            ->where('studio_id', $studioId)
            ->whereKey($roomId)
            ->value('name');
        if ($roomName === '') {
            $roomName = 'Room #'.$roomId;
        }

        try {
            DB::transaction(function () use ($studioId, $validated, $weekdays, $roomName) {
                foreach ($weekdays as $weekday) {
                    $data = array_merge($validated, [
                        'day_of_week' => (int) $weekday,
                    ]);

                    try {
                        $this->assertNoOverlap($studioId, $data);
                    } catch (ValidationException $e) {
                        $weekdayName = $this->weekdayName((int) $weekday);
                        $start = is_string($data['start_time'] ?? null) ? substr((string) $data['start_time'], 0, 5) : '';
                        $end = is_string($data['end_time'] ?? null) ? substr((string) $data['end_time'], 0, 5) : '';
                        $timeLabel = $start !== '' && $end !== '' ? ($start.'-'.$end) : '';
                        $message = trim($weekdayName.' '.$timeLabel.' '.$roomName).' conflicts with existing regular class.';
                        throw ValidationException::withMessages([
                            'weekdays' => [$message],
                        ]);
                    }

                    RegularClass::query()->create(array_merge($data, [
                        'studio_id' => $studioId,
                    ]));
                }
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->to(url('/regular-classes'))
            ->with('success', 'Regular class created successfully.');
    }

    public function edit(RegularClass $regularClass)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $regularClass->studio_id === $studioId, 404);

        return view('dance-studio.regular-classes.edit', [
            'regularClass' => $regularClass,
            'rooms' => $this->rooms($studioId),
            'teachers' => $this->teachers($studioId),
            'classTypes' => $this->classTypes($studioId),
        ]);
    }

    public function update(Request $request, RegularClass $regularClass)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $regularClass->studio_id === $studioId, 404);

        $data = $this->validatedSingle($request, (bool) $regularClass->is_active);
        $this->assertNoOverlap($studioId, $data, $regularClass->id);

        $regularClass->fill($data)->save();

        return redirect()
            ->to(url('/regular-classes'))
            ->with('success', 'Regular class updated successfully.');
    }

    public function destroy(RegularClass $regularClass)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $regularClass->studio_id === $studioId, 404);

        $regularClass->delete();

        return redirect()
            ->to(url('/regular-classes'))
            ->with('success', 'Regular class deleted successfully.');
    }

    private function validatedStore(Request $request): array
    {
        $data = $request->validate([
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['required', 'integer', 'min:1', 'max:7', 'distinct'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'room_id' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['nullable', 'integer', 'min:1'],
            'class_type_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:120'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['weekdays'] = array_values(array_map('intval', $data['weekdays']));
        $data['teacher_id'] = $data['teacher_id'] ?? null;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $data['start_time'] = $this->normalizeTime($data['start_time']);
        $data['end_time'] = $this->normalizeTime($data['end_time']);

        if ($data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => ['End time must be later than start time.'],
            ]);
        }

        if (! $request->filled('starts_on')) {
            $data['starts_on'] = null;
        }
        if (! $request->filled('ends_on')) {
            $data['ends_on'] = null;
        }

        $startsOn = $data['starts_on'] ?? null;
        $endsOn = $data['ends_on'] ?? null;
        if ($startsOn !== null && $endsOn !== null && $endsOn < $startsOn) {
            throw ValidationException::withMessages([
                'ends_on' => ['Ends on must be on or after starts on.'],
            ]);
        }

        return $data;
    }

    private function validatedSingle(Request $request, bool $defaultIsActive): array
    {
        $data = $request->validate([
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'room_id' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['nullable', 'integer', 'min:1'],
            'class_type_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:120'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['day_of_week'] = (int) $data['day_of_week'];
        $data['teacher_id'] = $data['teacher_id'] ?? null;
        $data['is_active'] = (bool) ($data['is_active'] ?? $defaultIsActive);

        $data['start_time'] = $this->normalizeTime($data['start_time']);
        $data['end_time'] = $this->normalizeTime($data['end_time']);

        if ($data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => ['End time must be later than start time.'],
            ]);
        }

        if (!$request->filled('starts_on')) {
            $data['starts_on'] = null;
        }
        if (!$request->filled('ends_on')) {
            $data['ends_on'] = null;
        }

        $startsOn = $data['starts_on'] ?? null;
        $endsOn = $data['ends_on'] ?? null;
        if ($startsOn !== null && $endsOn !== null && $endsOn < $startsOn) {
            throw ValidationException::withMessages([
                'ends_on' => ['Ends on must be on or after starts on.'],
            ]);
        }

        return $data;
    }

    private function assertNoOverlap(int $studioId, array $data, ?int $ignoreId = null): void
    {
        if (($data['is_active'] ?? false) !== true) {
            return;
        }

        $rangeStart = $data['starts_on'] ?? '0001-01-01';
        $rangeEnd = $data['ends_on'] ?? '9999-12-31';

        $base = RegularClass::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where('day_of_week', (int) $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->where(function ($query) use ($rangeEnd) {
                $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', $rangeEnd);
            })
            ->where(function ($query) use ($rangeStart) {
                $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $rangeStart);
            });

        if ($ignoreId !== null) {
            $base->where('id', '!=', $ignoreId);
        }

        $roomConflict = (clone $base)->where('room_id', (int) $data['room_id'])->exists();
        if ($roomConflict) {
            throw ValidationException::withMessages([
                'room_id' => ['This room has an overlapping regular class for the selected day/time.'],
            ]);
        }

        $teacherId = $data['teacher_id'] ?? null;
        if ($teacherId !== null) {
            $teacherConflict = (clone $base)->where('teacher_id', (int) $teacherId)->exists();
            if ($teacherConflict) {
                throw ValidationException::withMessages([
                    'teacher_id' => ['This teacher has an overlapping regular class for the selected day/time.'],
                ]);
            }
        }
    }

    private function rooms(int $studioId)
    {
        return Room::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    private function teachers(int $studioId)
    {
        return Teacher::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'display_name', 'user_id']);
    }

    private function classTypes(int $studioId)
    {
        return ClassType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('kind', ['regular', 'both'])->orWhere('name', 'Regular Class');
            })
            ->orderByRaw("CASE WHEN kind = 'regular' THEN 0 WHEN kind = 'both' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->get(['id', 'name', 'kind']);
    }

    private function normalizeTime(string $time): string
    {
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time.':00';
        }

        return $time;
    }

    private function weekdayName(int $isoWeekday): string
    {
        return match ($isoWeekday) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => (string) $isoWeekday,
        };
    }
}

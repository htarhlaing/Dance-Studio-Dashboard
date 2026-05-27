<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PackageType;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Studio;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;

class StudioSettingsController extends Controller
{
    public function index()
    {
        $resolver = app(CurrentStudioResolver::class);
        $studio = $resolver->studio() ?? Studio::query()->findOrFail($resolver->id());

        $privateLessonClassType = ClassType::query()
            ->where('studio_id', $studio->id)
            ->where('kind', 'private')
            ->first();

        if ($privateLessonClassType === null) {
            $privateLessonClassType = ClassType::query()
                ->where('studio_id', $studio->id)
                ->where('name', 'Private Lesson')
                ->first();
        }

        $activeRoomsCount = (int) Room::query()
            ->where('studio_id', $studio->id)
            ->where('is_active', true)
            ->count();

        $activeClassTypesCount = (int) ClassType::query()
            ->where('studio_id', $studio->id)
            ->where('is_active', true)
            ->count();

        $activePackageTypesCount = (int) PackageType::query()
            ->where('studio_id', $studio->id)
            ->where('is_active', true)
            ->count();

        return view('dance-studio.studio-settings.index', [
            'studio' => $studio,
            'privateLessonClassType' => $privateLessonClassType,
            'activeRoomsCount' => $activeRoomsCount,
            'activeClassTypesCount' => $activeClassTypesCount,
            'activePackageTypesCount' => $activePackageTypesCount,
        ]);
    }

    public function edit(Request $request)
    {
        $resolver = app(CurrentStudioResolver::class);
        $studio = $resolver->studio() ?? Studio::query()->findOrFail($resolver->id());

        $privateLessonClassType = ClassType::query()
            ->where('studio_id', $studio->id)
            ->where('kind', 'private')
            ->first();

        if ($privateLessonClassType === null) {
            $privateLessonClassType = ClassType::query()
                ->where('studio_id', $studio->id)
                ->where('name', 'Private Lesson')
                ->first();
        }

        return view('dance-studio.studio-settings.edit', [
            'studio' => $studio,
            'privateLessonClassType' => $privateLessonClassType,
        ]);
    }

    public function update(Request $request)
    {
        $resolver = app(CurrentStudioResolver::class);
        $studio = $resolver->studio() ?? Studio::query()->findOrFail($resolver->id());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business_start_time' => ['nullable', 'date_format:H:i'],
            'business_end_time' => ['nullable', 'date_format:H:i'],
            'booking_interval_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'timezone' => ['required', 'string', 'max:64'],
            'currency' => ['required', 'string', 'size:3'],
            'private_lesson_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'minimum_booking_notice_hours' => ['required', 'integer', 'min:0', 'max:8760'],
            'minimum_reschedule_notice_hours' => ['required', 'integer', 'min:0', 'max:8760'],
            'minimum_cancel_notice_hours' => ['required', 'integer', 'min:0', 'max:8760'],
        ]);

        $studio->fill([
            'name' => $validated['name'],
            'business_start_time' => $validated['business_start_time'] !== null ? ($validated['business_start_time'].':00') : null,
            'business_end_time' => $validated['business_end_time'] !== null ? ($validated['business_end_time'].':00') : null,
            'booking_interval_minutes' => $validated['booking_interval_minutes'],
            'timezone' => $validated['timezone'],
            'currency' => strtoupper($validated['currency']),
            'minimum_booking_notice_hours' => (int) $validated['minimum_booking_notice_hours'],
            'minimum_reschedule_notice_hours' => (int) $validated['minimum_reschedule_notice_hours'],
            'minimum_cancel_notice_hours' => (int) $validated['minimum_cancel_notice_hours'],
            'allow_admin_frontdesk_override_notice' => $request->boolean('allow_admin_frontdesk_override_notice'),
            'teacher_can_create_booking' => $request->boolean('teacher_can_create_booking'),
        ]);
        $studio->save();

        $privateLessonClassType = ClassType::query()
            ->where('studio_id', $studio->id)
            ->where('kind', 'private')
            ->first();

        if ($privateLessonClassType === null) {
            $privateLessonClassType = ClassType::query()
                ->where('studio_id', $studio->id)
                ->where('name', 'Private Lesson')
                ->first();
        }

        if ($privateLessonClassType !== null) {
            $privateLessonClassType->default_duration_minutes = $validated['private_lesson_duration_minutes'];
            $privateLessonClassType->save();
        }

        return redirect()->back()->with('success', 'Studio settings saved successfully.');
    }
}

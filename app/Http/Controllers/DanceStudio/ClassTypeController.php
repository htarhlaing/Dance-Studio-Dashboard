<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\ClassType;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassTypeController extends Controller
{
    private const KINDS = ['regular', 'private', 'both', 'workshop', 'kids'];

    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $name = $request->query('name');
        $name = is_string($name) ? trim($name) : '';

        $kind = $request->query('kind');
        $kind = is_string($kind) ? trim($kind) : '';
        $kind = in_array($kind, self::KINDS, true) ? $kind : '';

        $isActive = $request->query('is_active');
        $isActive = $isActive === '1' ? true : ($isActive === '0' ? false : null);

        $query = ClassType::query()
            ->where('studio_id', $studioId)
            ->orderBy('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($kind !== '') {
            $query->where('kind', $kind);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $classTypes = $query->get();

        return view('dance-studio.class-types.index', [
            'classTypes' => $classTypes,
            'name' => $name,
            'kind' => $kind,
            'isActive' => $isActive,
            'kinds' => self::KINDS,
        ]);
    }

    public function create()
    {
        return view('dance-studio.class-types.create', [
            'kinds' => self::KINDS,
        ]);
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('class_types', 'name')->where('studio_id', $studioId)],
            'kind' => ['nullable', 'string', 'max:20'],
            'default_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'default_deduct_units' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $kind = $data['kind'] ?? null;
        $kind = is_string($kind) ? trim($kind) : '';
        $kind = in_array($kind, self::KINDS, true) ? $kind : 'both';

        ClassType::query()->create([
            'studio_id' => $studioId,
            'name' => $data['name'],
            'kind' => $kind,
            'default_duration_minutes' => $data['default_duration_minutes'] !== null ? (int) $data['default_duration_minutes'] : null,
            'default_deduct_units' => $data['default_deduct_units'] !== null ? (int) $data['default_deduct_units'] : 1,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->to(url('/class-types'))
            ->with('success', 'Class type created successfully.');
    }

    public function edit(ClassType $classType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $classType->studio_id === $studioId, 404);

        return view('dance-studio.class-types.edit', [
            'classType' => $classType,
            'kinds' => self::KINDS,
        ]);
    }

    public function update(Request $request, ClassType $classType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $classType->studio_id === $studioId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('class_types', 'name')->where('studio_id', $studioId)->ignore($classType->id)],
            'kind' => ['nullable', 'string', 'max:20'],
            'default_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'default_deduct_units' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $kind = $data['kind'] ?? null;
        $kind = is_string($kind) ? trim($kind) : '';
        $kind = in_array($kind, self::KINDS, true) ? $kind : 'both';

        $classType->fill([
            'name' => $data['name'],
            'kind' => $kind,
            'default_duration_minutes' => $data['default_duration_minutes'] !== null ? (int) $data['default_duration_minutes'] : null,
            'default_deduct_units' => $data['default_deduct_units'] !== null ? (int) $data['default_deduct_units'] : 1,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        return redirect()
            ->to(url('/class-types'))
            ->with('success', 'Class type updated successfully.');
    }

    public function destroy(ClassType $classType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $classType->studio_id === $studioId, 404);

        $classType->forceFill(['is_active' => false])->save();

        return redirect()
            ->to(url('/class-types'))
            ->with('success', 'Class type deactivated successfully.');
    }
}


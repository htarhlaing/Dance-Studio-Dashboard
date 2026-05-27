<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PackageType;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageTypeController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $name = $request->query('name');
        $name = is_string($name) ? trim($name) : '';

        $isActive = $request->query('is_active');
        $isActive = $isActive === '1' ? true : ($isActive === '0' ? false : null);

        $query = PackageType::query()
            ->where('studio_id', $studioId)
            ->orderBy('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $packageTypes = $query->get();

        return view('dance-studio.package-types.index', [
            'packageTypes' => $packageTypes,
            'name' => $name,
            'isActive' => $isActive,
        ]);
    }

    public function create()
    {
        $defaultCurrency = app(CurrentStudioResolver::class)->studio()?->currency;

        return view('dance-studio.package-types.create', [
            'defaultCurrency' => $defaultCurrency ?? 'USD',
        ]);
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('package_types', 'name')->where('studio_id', $studioId)],
            'lessons_count' => ['required', 'integer', 'min:1'],
            'validity_days' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PackageType::query()->create([
            'studio_id' => $studioId,
            'name' => $data['name'],
            'lessons_count' => (int) $data['lessons_count'],
            'validity_days' => $data['validity_days'] !== null ? (int) $data['validity_days'] : null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->to(url('/package-types'))
            ->with('success', 'Package type created successfully.');
    }

    public function edit(PackageType $packageType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $packageType->studio_id === $studioId, 404);

        return view('dance-studio.package-types.edit', [
            'packageType' => $packageType,
        ]);
    }

    public function update(Request $request, PackageType $packageType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $packageType->studio_id === $studioId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('package_types', 'name')->where('studio_id', $studioId)->ignore($packageType->id)],
            'lessons_count' => ['required', 'integer', 'min:1'],
            'validity_days' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $packageType->fill([
            'name' => $data['name'],
            'lessons_count' => (int) $data['lessons_count'],
            'validity_days' => $data['validity_days'] !== null ? (int) $data['validity_days'] : null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        return redirect()
            ->to(url('/package-types'))
            ->with('success', 'Package type updated successfully.');
    }

    public function destroy(PackageType $packageType)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $packageType->studio_id === $studioId, 404);

        $packageType->forceFill(['is_active' => false])->save();

        return redirect()
            ->to(url('/package-types'))
            ->with('success', 'Package type deactivated successfully.');
    }
}


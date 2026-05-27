<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\PackageTransaction;
use App\Models\DanceStudio\PackageType;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Services\DanceStudio\CurrentStudioResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentPackageController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $studentId = $request->query('student_id');
        $studentId = is_numeric($studentId) ? (int) $studentId : null;

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;

        $query = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->with(['student', 'packageType'])
            ->orderBy('purchased_at', 'desc')
            ->orderBy('id', 'desc');

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $studentPackages = $query->get();

        $students = Student::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->get(['id', 'name']);

        $availableStatuses = StudentPackage::query()
            ->where('studio_id', $studioId)
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->filter()
            ->values();

        return view('dance-studio.student-packages.index', [
            'studentPackages' => $studentPackages,
            'students' => $students,
            'studentId' => $studentId,
            'status' => $status,
            'availableStatuses' => $availableStatuses,
        ]);
    }

    public function create()
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $students = Student::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        $packageTypes = PackageType::query()
            ->where('studio_id', $studioId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'lessons_count', 'validity_days']);

        $now = now();

        return view('dance-studio.student-packages.create', [
            'students' => $students,
            'packageTypes' => $packageTypes,
            'defaultPurchasedAt' => $now->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'min:1'],
            'package_type_id' => ['required', 'integer', 'min:1'],
            'total_units' => ['required', 'integer', 'min:1'],
            'remaining_units' => ['required', 'integer', 'min:0'],
            'purchased_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $studentId = (int) $data['student_id'];
        $packageTypeId = (int) $data['package_type_id'];

        $studentStudioId = (int) Student::query()->whereKey($studentId)->value('studio_id');
        if ($studentStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'student_id' => ['Student does not belong to this studio.'],
            ]);
        }

        $packageTypeStudioId = (int) PackageType::query()->whereKey($packageTypeId)->value('studio_id');
        if ($packageTypeStudioId !== $studioId) {
            throw ValidationException::withMessages([
                'package_type_id' => ['Package type does not belong to this studio.'],
            ]);
        }

        $purchasedAt = CarbonImmutable::parse((string) $data['purchased_at']);
        $expiresAt = $data['expires_at'] !== null ? CarbonImmutable::parse((string) $data['expires_at']) : null;

        if ($expiresAt !== null && $expiresAt->lt($purchasedAt)) {
            throw ValidationException::withMessages([
                'expires_at' => ['Expires at cannot be earlier than purchased at.'],
            ]);
        }

        $totalUnits = (int) $data['total_units'];
        $remainingUnits = (int) $data['remaining_units'];

        if ($remainingUnits > $totalUnits) {
            throw ValidationException::withMessages([
                'remaining_units' => ['Remaining units cannot be greater than total units.'],
            ]);
        }

        $status = $data['status'] ?? null;
        $status = is_string($status) && $status !== '' ? $status : 'active';

        $studentPackage = StudentPackage::query()->create([
            'studio_id' => $studioId,
            'student_id' => $studentId,
            'package_type_id' => $packageTypeId,
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
            'total_units' => $totalUnits,
            'remaining_units' => $remainingUnits,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ]);

        PackageTransaction::query()->create([
            'studio_id' => $studioId,
            'student_id' => $studentId,
            'student_package_id' => $studentPackage->id,
            'type' => 'purchase',
            'units_delta' => $remainingUnits,
            'balance_after' => $remainingUnits,
            'occurred_at' => $purchasedAt,
            'notes' => 'Package created / renewed',
        ]);

        return redirect()
            ->to(url('/student-packages'))
            ->with('success', 'Student package created successfully.');
    }
}

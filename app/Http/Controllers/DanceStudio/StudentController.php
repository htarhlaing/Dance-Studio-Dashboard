<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\Student;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $name = $request->query('name');
        $name = is_string($name) ? trim($name) : '';

        $isActive = $request->query('is_active');
        $isActive = $isActive === '1' ? true : ($isActive === '0' ? false : null);

        $query = Student::query()
            ->where('studio_id', $studioId)
            ->orderBy('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $students = $query->get();

        return view('dance-studio.students.index', [
            'students' => $students,
            'name' => $name,
            'isActive' => $isActive,
        ]);
    }

    public function create()
    {
        return view('dance-studio.students.create');
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Student::query()->create([
            'studio_id' => $studioId,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->to(url('/students'))
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $student->studio_id === $studioId, 404);

        return view('dance-studio.students.edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $student->studio_id === $studioId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $student->fill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        return redirect()
            ->to(url('/students'))
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        abort_unless((int) $student->studio_id === $studioId, 404);

        $student->delete();

        return redirect()
            ->to(url('/students'))
            ->with('success', 'Student deleted successfully.');
    }
}


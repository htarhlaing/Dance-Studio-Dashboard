<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $name = $request->query('name');
        $name = is_string($name) ? trim($name) : '';

        $isActive = $request->query('is_active');
        $isActive = $isActive === '1' ? true : ($isActive === '0' ? false : null);

        $query = Teacher::query()
            ->where('studio_id', $studioId)
            ->with(['user'])
            ->orderBy('id', 'desc');

        if ($name !== '') {
            $query->where(function ($q) use ($name) {
                $q
                    ->where('display_name', 'like', '%'.$name.'%')
                    ->orWhereHas('user', function ($uq) use ($name) {
                        $uq->where('name', 'like', '%'.$name.'%');
                    });
            });
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $teachers = $query->get();

        return view('dance-studio.teachers.index', [
            'teachers' => $teachers,
            'name' => $name,
            'isActive' => $isActive,
        ]);
    }

    public function create()
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $users = User::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('dance-studio.teachers.create', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $userStudioId = (int) User::query()->whereKey((int) $data['user_id'])->value('studio_id');
        abort_unless($userStudioId === $studioId, 404);

        Teacher::query()->create([
            'studio_id' => $studioId,
            'user_id' => (int) $data['user_id'],
            'display_name' => $data['display_name'],
            'bio' => $data['bio'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->to(url('/teachers'))
            ->with('success', 'Teacher created successfully.');
    }

    public function edit(Teacher $teacher)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $teacher->studio_id === $studioId, 404);

        $users = User::query()
            ->where('studio_id', $studioId)
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('dance-studio.teachers.edit', [
            'teacher' => $teacher,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $teacher->studio_id === $studioId, 404);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $userStudioId = (int) User::query()->whereKey((int) $data['user_id'])->value('studio_id');
        abort_unless($userStudioId === $studioId, 404);

        $teacher->fill([
            'user_id' => (int) $data['user_id'],
            'display_name' => $data['display_name'],
            'bio' => $data['bio'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        return redirect()
            ->to(url('/teachers'))
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $teacher->studio_id === $studioId, 404);

        $teacher->delete();

        return redirect()
            ->to(url('/teachers'))
            ->with('success', 'Teacher deleted successfully.');
    }
}


<?php

namespace App\Http\Controllers\DanceStudio;

use App\Http\Controllers\Controller;
use App\Models\DanceStudio\Room;
use App\Services\DanceStudio\CurrentStudioResolver;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $name = $request->query('name');
        $name = is_string($name) ? trim($name) : '';

        $isActive = $request->query('is_active');
        $isActive = $isActive === '1' ? true : ($isActive === '0' ? false : null);

        $query = Room::query()
            ->where('studio_id', $studioId)
            ->orderBy('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%'.$name.'%');
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $rooms = $query->get();

        return view('dance-studio.rooms.index', [
            'rooms' => $rooms,
            'name' => $name,
            'isActive' => $isActive,
        ]);
    }

    public function create()
    {
        return view('dance-studio.rooms.create');
    }

    public function store(Request $request)
    {
        $studioId = app(CurrentStudioResolver::class)->id();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Room::query()->create([
            'studio_id' => $studioId,
            'name' => $data['name'],
            'capacity' => $data['capacity'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->to(url('/rooms'))
            ->with('success', 'Room created successfully.');
    }

    public function edit(Room $room)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $room->studio_id === $studioId, 404);

        return view('dance-studio.rooms.edit', [
            'room' => $room,
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $room->studio_id === $studioId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $room->fill([
            'name' => $data['name'],
            'capacity' => $data['capacity'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ])->save();

        return redirect()
            ->to(url('/rooms'))
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $studioId = app(CurrentStudioResolver::class)->id();
        abort_unless((int) $room->studio_id === $studioId, 404);

        $room->delete();

        return redirect()
            ->to(url('/rooms'))
            ->with('success', 'Room deleted successfully.');
    }
}


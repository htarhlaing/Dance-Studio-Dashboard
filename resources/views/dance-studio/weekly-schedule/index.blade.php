@extends('layouts.app')

@section('title', 'Weekly Schedule')

@push('styles')
    <style>
        .weekly-grid { overflow-x: auto; }
        .schedule-cell { min-width: 190px; }
        .schedule-empty { color: #9ca3af; font-size: 12px; }

        .schedule-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px 10px; background: #ffffff; margin: 0 0 8px; }
        .schedule-card:last-child { margin-bottom: 0; }
        .schedule-card-header { display: flex; gap: 8px; align-items: baseline; margin-bottom: 6px; }
        .schedule-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .schedule-badge-regular { background: #dbeafe; color: #1e40af; }
        .schedule-badge-private { background: #ffedd5; color: #9a3412; }
        .schedule-title { font-weight: 700; color: #111827; font-size: 13px; line-height: 1.25; }
        .schedule-line { color: #111827; font-size: 12px; line-height: 1.35; }
        .schedule-meta { color: #6b7280; font-size: 12px; line-height: 1.35; margin-top: 2px; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Weekly Schedule</h1>
            <div class="muted">Week: {{ $weekStart->toDateString() }} - {{ $weekStart->addDays(6)->toDateString() }}</div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/dashboard') }}">Dashboard</a>
            <a class="btn btn-secondary" href="{{ url('/availability') }}">Availability</a>
            <a class="btn btn-secondary" href="{{ url('/regular-classes') }}">Regular Classes</a>
            <a class="btn btn-secondary" href="{{ url('/private-bookings') }}">Private Bookings</a>
        </div>
    </div>

    <form method="get" action="{{ url('/weekly-schedule') }}">
        <div class="form-row">
            <div>
                <label for="week_start">Week Start (Monday)</label>
                <input id="week_start" name="week_start" type="date" value="{{ $weekStart->toDateString() }}">
            </div>
            <div>
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id">
                    <option value="">All Rooms</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected((string) $roomId === (string) $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="btn" type="submit">Go</button>
            </div>
        </div>
    </form>

    <div class="table-wrap weekly-grid">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 110px;">Time</th>
                    @foreach ($days as $day)
                        <th>
                            {{ $day['date']->format('l') }}
                            <div class="muted">{{ $day['date']->format('Y-m-d') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($slots as $slotIndex => $slot)
                    <tr>
                        <td class="muted">{{ $slot['label'] }}</td>
                        @foreach ($days as $dayIndex => $day)
                            @php
                                $items = $grid[$slotIndex][$dayIndex] ?? [];
                            @endphp
                            <td class="schedule-cell">
                                @if (count($items) === 0)
                                    <span class="schedule-empty">-</span>
                                @else
                                    @foreach ($items as $item)
                                        @include('dance-studio.weekly-schedule.partials.schedule-card', ['item' => $item])
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

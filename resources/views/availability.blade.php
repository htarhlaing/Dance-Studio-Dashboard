@extends('layouts.app')

@section('title', 'Availability')

@push('styles')
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background: #f8fafc; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #fff; }
            .available { background: #198754; }
            .regular { background: #0d6efd; }
            .private_booked { background: #fd7e14; }
            .completed { background: #6b7280; }
            .muted { color: #6b7280; font-size: 12px; margin-left: 8px; }
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; }
            select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
            button { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; }
            .book-link { display: inline-block; margin-left: 10px; padding: 2px 8px; border: 1px solid #111827; border-radius: 6px; color: #111827; text-decoration: none; font-size: 12px; }
        </style>
@endpush

@section('content')
        <h1>Availability</h1>
        <p class="muted">Business hours: {{ $businessStartTime }}-{{ $businessEndTime }}</p>
        <p class="muted">Private lesson duration: {{ $resolvedDurationMinutes }} minutes</p>
        <p class="muted">Booking interval: {{ $bookingIntervalMinutes }} minutes</p>

        <form method="get" action="{{ url('/availability') }}">
            <div class="row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ $date }}">
                </div>
                <div>
                    <label for="duration">Duration</label>
                    <select id="duration" name="duration">
                        @php
                            $selectedDuration = $duration ?? $resolvedDurationMinutes;
                        @endphp
                        @foreach ([30, 60, 90, 120] as $minutes)
                            <option value="{{ $minutes }}" @selected($selectedDuration === $minutes)>{{ $minutes }} minutes</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="studio_id" value="{{ $studioId }}">
                <div>
                    <button type="submit">Search</button>
                </div>
            </div>
        </form>

        @if (count($rooms) === 0)
            <p class="muted">No rooms found for this studio.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 120px;">Time</th>
                        @foreach ($rooms as $room)
                            <th>{{ $room->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($availability as $slot)
                        <tr>
                            <td>{{ $slot['time'] }}</td>
                            @php
                                $byRoomId = collect($slot['rooms'])->keyBy('room_id');
                            @endphp
                            @foreach ($rooms as $room)
                                @php
                                    $cell = $byRoomId->get($room->id);
                                    $status = $cell['status'] ?? 'available';
                                    $reason = $cell['reason'] ?? null;
                                @endphp
                                <td>
                                    <span class="badge {{ $status }}">{{ $status }}</span>
                                    @if ($reason)
                                        <span class="muted">{{ $reason }}</span>
                                    @endif
                                    @if ($status === 'available')
                                        @php
                                            [$startTime, $endTime] = explode('-', $slot['time']);
                                            $activeDuration = $duration ?? $resolvedDurationMinutes;
                                            $query = http_build_query([
                                                'date' => $date,
                                                'room_id' => $room->id,
                                                'start_time' => $startTime,
                                                'end_time' => $endTime,
                                                'duration' => $activeDuration,
                                            ]);
                                        @endphp
                                        <a class="book-link" href="{{ url('/private-bookings/create') }}?{{ $query }}">Book</a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
@endsection

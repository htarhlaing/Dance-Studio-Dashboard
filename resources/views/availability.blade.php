@extends('layouts.app')

@section('title', 'Availability')

@push('styles')
        <style>
            .cell-hint { color: #6b7280; font-size: 12px; margin-left: 8px; }
        </style>
@endpush

@section('content')
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Availability</h1>
                <div class="muted">Business hours: {{ $businessStartTime }}-{{ $businessEndTime }}</div>
                <div class="muted">Private lesson duration: {{ $resolvedDurationMinutes }} minutes</div>
                <div class="muted">Booking interval: {{ $bookingIntervalMinutes }} minutes</div>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="{{ url('/regular-classes') }}">Manage Regular Classes</a>
            </div>
        </div>

        <form method="get" action="{{ url('/availability') }}">
            <div class="form-row">
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
                    <button class="btn" type="submit">Search</button>
                </div>
            </div>
        </form>

        @if (count($rooms) === 0)
            <p class="muted">No rooms found for this studio.</p>
        @elseif (count($availability) === 0)
            <p class="muted">No records found.</p>
        @else
            <div class="table-wrap">
                <table class="table">
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
                                        $statusClass = 'status-'.$status;
                                        $statusLabel = match ($status) {
                                            'available' => 'Available',
                                            'regular' => 'Regular Class',
                                            'private_booked' => 'Booked',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                            default => $status,
                                        };
                                    @endphp
                                    <td>
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if ($reason)
                                            <span class="cell-hint">{{ $reason }}</span>
                                        @endif
                                        @if ($status === 'available' && $canCreateBooking)
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
                                            <a class="btn btn-small btn-book" href="{{ url('/private-bookings/create') }}?{{ $query }}">Book</a>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
@endsection

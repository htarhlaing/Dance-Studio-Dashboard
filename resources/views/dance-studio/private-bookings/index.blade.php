@extends('layouts.app')

@section('title', 'Private Bookings')

@section('content')
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Private Bookings</h1>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
                @if ($canSeeCreateEntry)
                    <a class="btn" href="{{ $findAvailabilityUrl }}">Find Available Slot</a>
                    @if ($showManualCreate)
                        <a class="btn btn-secondary" href="{{ url('/private-bookings/create') }}">Manual Create</a>
                    @endif
                @endif
            </div>
        </div>

        <form method="get" action="{{ url('/private-bookings') }}">
            <div class="form-row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ $date }}">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s)
                            <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 120px;">Time</th>
                        <th>Room</th>
                        <th>Student</th>
                        <th>Teacher</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 170px;">Created At</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php
                            $statusClass = 'status-'.($booking->status ?? 'pending');
                            $start = $booking->start_at?->format('H:i');
                            $end = $booking->end_at?->format('H:i');
                            $now = now();
                            $displayStatus = 'Upcoming';
                            if (($booking->status ?? '') === 'cancelled') {
                                $displayStatus = 'Cancelled';
                            } elseif (($booking->status ?? '') === 'completed') {
                                $displayStatus = 'Completed';
                            } else {
                                if ($booking->start_at !== null && $booking->end_at !== null) {
                                    if ($now->lt($booking->start_at)) {
                                        $displayStatus = 'Upcoming';
                                    } elseif ($now->gte($booking->start_at) && $now->lte($booking->end_at)) {
                                        $displayStatus = 'Ongoing';
                                    } elseif ($now->gt($booking->end_at)) {
                                        $displayStatus = 'Pending Attendance';
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $booking->start_at?->format('Y-m-d') }}</td>
                            <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                            <td>{{ $booking->room?->name }}</td>
                            <td>{{ $booking->student?->name }}</td>
                            <td>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</td>
                            <td>
                                <span class="badge">{{ $displayStatus }}</span>
                                <div class="muted">{{ $booking->status }}</div>
                            </td>
                            <td class="muted">{{ $booking->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if (in_array($booking->status, ['pending', 'confirmed'], true))
                                    <div class="actions">
                                        <a class="btn btn-secondary btn-small" href="{{ url('/private-bookings/'.$booking->id.'/reschedule') }}">Reschedule</a>
                                        <form method="post" action="{{ url('/private-bookings/'.$booking->id.'/cancel') }}" style="display: inline-block;">
                                            @csrf
                                            <button class="btn btn-secondary btn-small" type="submit">Cancel</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
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

@extends('layouts.app')

@section('title', 'Private Bookings')

@push('styles')
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background: #f8fafc; }
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input, select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
            button, .btn { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn-secondary { background: #fff; color: #111827; }
            .muted { color: #6b7280; font-size: 12px; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #111827; background: #e5e7eb; }
            .status-pending { background: #fef3c7; color: #92400e; }
            .status-confirmed { background: #dbeafe; color: #1e40af; }
            .status-completed { background: #d1fae5; color: #065f46; }
            .status-cancelled { background: #e5e7eb; color: #374151; }
        </style>
@endpush

@section('content')
        <div class="row" style="justify-content: space-between;">
            <div>
                <h1 style="margin: 0;">Private Bookings</h1>
            </div>
            <div class="row" style="margin: 0;">
                <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
                <a class="btn" href="{{ url('/private-bookings/create') }}">Create Booking</a>
            </div>
        </div>

        <form method="get" action="{{ url('/private-bookings') }}">
            <div class="row">
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
                    <button type="submit">Filter</button>
                </div>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 170px;">Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    @php
                        $statusClass = 'status-'.($booking->status ?? 'pending');
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    @endphp
                    <tr>
                        <td>{{ $booking->start_at?->format('Y-m-d') }}</td>
                        <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                        <td>{{ $booking->room?->name }}</td>
                        <td>{{ $booking->student?->name }}</td>
                        <td>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $booking->status }}</span></td>
                        <td class="muted">{{ $booking->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">No private bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection

@extends('layouts.app')

@section('title', 'Attendance')

@push('styles')
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            th { background: #f8fafc; }
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
            button, .btn { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn-secondary { background: #fff; color: #111827; }
            .muted { color: #6b7280; font-size: 12px; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #111827; background: #e5e7eb; }
            .status-pending { background: #fef3c7; color: #92400e; }
            .status-confirmed { background: #dbeafe; color: #1e40af; }
            .status-completed { background: #d1fae5; color: #065f46; }
            .status-cancelled { background: #e5e7eb; color: #374151; }
            .actions { display: flex; flex-wrap: wrap; gap: 6px; }
            .btn-small { padding: 4px 8px; font-size: 12px; }
            .btn-present { background: #16a34a; border-color: #16a34a; }
            .btn-absent { background: #eab308; border-color: #eab308; color: #111827; }
            .btn-leave { background: #2563eb; border-color: #2563eb; }
            .btn-cancelled { background: #6b7280; border-color: #6b7280; }
            .btn-disabled { opacity: 0.5; cursor: not-allowed; }
            .alert { padding: 10px 12px; border-radius: 8px; margin: 12px 0; }
            .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
            .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        </style>
@endpush

@section('content')
        <div class="row" style="justify-content: space-between;">
            <div>
                <h1 style="margin: 0;">Attendance</h1>
            </div>
            <div class="row" style="margin: 0;">
                <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
                <a class="btn btn-secondary" href="{{ url('/private-bookings') }}">Private Bookings</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="get" action="{{ url('/attendance') }}">
            <div class="row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ $date }}">
                </div>
                <div>
                    <button type="submit">Search</button>
                </div>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th style="width: 140px;">Booking Status</th>
                    <th style="width: 320px;">Attendance Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    @php
                        $statusClass = 'status-'.($booking->status ?? 'pending');
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                        $isFinal = in_array($booking->status, ['completed', 'cancelled'], true);
                    @endphp
                    <tr>
                        <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                        <td>{{ $booking->room?->name }}</td>
                        <td>{{ $booking->student?->name }}</td>
                        <td>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $booking->status }}</span></td>
                        <td>
                            @if ($isFinal)
                                <span class="muted">No actions available.</span>
                            @else
                                <div class="actions">
                                    @foreach (['present', 'absent', 'leave', 'cancelled'] as $s)
                                        @php
                                            $btnClass = match ($s) {
                                                'present' => 'btn-present',
                                                'absent' => 'btn-absent',
                                                'leave' => 'btn-leave',
                                                'cancelled' => 'btn-cancelled',
                                                default => '',
                                            };
                                        @endphp
                                        <form method="post" action="{{ url('/attendance/private-bookings/'.$booking->id.'/mark') }}">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $s }}">
                                            <button class="btn btn-small {{ $btnClass }}" type="submit">{{ ucfirst($s) }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No private bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection

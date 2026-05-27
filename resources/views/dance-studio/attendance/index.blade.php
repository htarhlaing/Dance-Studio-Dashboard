@extends('layouts.app')

@section('title', 'Attendance')

@push('styles')
        <style>
            .actions { display: flex; flex-wrap: wrap; gap: 6px; }
            .btn-present { background: #16a34a; border-color: #16a34a; }
            .btn-absent { background: #eab308; border-color: #eab308; color: #111827; }
            .btn-leave { background: #2563eb; border-color: #2563eb; }
            .btn-cancelled { background: #6b7280; border-color: #6b7280; }
            .btn-disabled { opacity: 0.5; cursor: not-allowed; }
        </style>
@endpush

@section('content')
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Attendance</h1>
            </div>
            <div class="page-actions">
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
            <div class="form-row">
                <div>
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ $date }}">
                </div>
                <div>
                    <button class="btn" type="submit">Search</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
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
                            <td colspan="6" class="empty-state">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
@endsection

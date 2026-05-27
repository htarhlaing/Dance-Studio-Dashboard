@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin-top: 0;">Dashboard</h1>
            <div class="muted">Today: {{ $today->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <p class="stat-label">Today Private Bookings</p>
            <p class="stat-value">{{ $todayPrivateBookingsCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending Bookings</p>
            <p class="stat-value">{{ $pendingBookingsCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Completed Today</p>
            <p class="stat-value">{{ $completedTodayCount }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Low Packages</p>
            <p class="stat-value">{{ $lowPackagesCount }}</p>
        </div>
    </div>

    <h2 style="margin: 0 0 10px;">Today Private Bookings</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                    <th style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($todayPrivateBookings as $booking)
                    @php
                        $statusValue = $booking->status ?? '';
                        $statusClass = 'status-'.($statusValue ?: 'pending');
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    @endphp
                    <tr>
                        <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                        <td>{{ $booking->room?->name }}</td>
                        <td>{{ $booking->student?->name }}</td>
                        <td>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusValue }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 style="margin: 18px 0 10px;">Pending Bookings</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 110px;">Date</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Student</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingBookings as $booking)
                    @php
                        $start = $booking->start_at?->format('H:i');
                        $end = $booking->end_at?->format('H:i');
                    @endphp
                    <tr>
                        <td>{{ $booking->start_at?->format('Y-m-d') }}</td>
                        <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                        <td>{{ $booking->room?->name }}</td>
                        <td>{{ $booking->student?->name }}</td>
                        <td>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>

    <h2 style="margin: 18px 0 10px;">Low Remaining Packages</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Package Type</th>
                    <th style="width: 160px;">Remaining Lessons</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lowRemainingPackages as $pkg)
                    @php
                        $remaining = (int) ($pkg->remaining_units ?? 0);
                        $remainingClass = $remaining <= 0 ? 'remaining-empty' : 'remaining-warn';
                    @endphp
                    <tr>
                        <td>{{ $pkg->student?->name }}</td>
                        <td>{{ $pkg->packageType?->name }}</td>
                        <td><span class="badge {{ $remainingClass }}">{{ $remaining }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>
@endsection

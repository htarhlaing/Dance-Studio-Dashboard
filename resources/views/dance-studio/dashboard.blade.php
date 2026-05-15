@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
        .card-title { color: #6b7280; font-size: 12px; margin: 0 0 6px; }
        .card-value { font-size: 24px; font-weight: 700; margin: 0; color: #111827; }
        .section { margin-top: 18px; }
        table { border-collapse: collapse; width: 100%; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .muted { color: #6b7280; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #111827; background: #e5e7eb; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
        .remaining-warn { background: #fef3c7; color: #92400e; }
        .remaining-empty { background: #fee2e2; color: #991b1b; }
        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px) { .grid { grid-template-columns: repeat(1, minmax(0, 1fr)); } }
    </style>
@endpush

@section('content')
    <h1 style="margin-top: 0;">Dance Studio Dashboard</h1>
    <p class="muted">Today: {{ $today->format('Y-m-d') }}</p>

    <div class="grid">
        <div class="card">
            <p class="card-title">Today Private Bookings</p>
            <p class="card-value">{{ $todayPrivateBookingsCount }}</p>
        </div>
        <div class="card">
            <p class="card-title">Pending Bookings</p>
            <p class="card-value">{{ $pendingBookingsCount }}</p>
        </div>
        <div class="card">
            <p class="card-title">Completed Today</p>
            <p class="card-value">{{ $completedTodayCount }}</p>
        </div>
        <div class="card">
            <p class="card-title">Low Packages</p>
            <p class="card-value">{{ $lowPackagesCount }}</p>
        </div>
    </div>

    <div class="section">
        <h2 style="margin: 0 0 10px;">Today Private Bookings</h2>
        <table>
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
                    <tr><td colspan="5" class="muted">No bookings today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 style="margin: 0 0 10px;">Pending Bookings</h2>
        <table>
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
                    <tr><td colspan="5" class="muted">No pending bookings.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>
    </div>

    <div class="section">
        <h2 style="margin: 0 0 10px;">Low Remaining Packages</h2>
        <table>
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
                    <tr><td colspan="3" class="muted">No low remaining packages.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p class="muted" style="margin: 8px 0 0;">Showing up to 20 items.</p>
    </div>
@endsection


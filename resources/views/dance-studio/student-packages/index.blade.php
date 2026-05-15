@extends('layouts.app')

@section('title', 'Student Packages')

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
            .status-active { background: #d1fae5; color: #065f46; }
            .status-expired { background: #e5e7eb; color: #374151; }
            .status-void { background: #fee2e2; color: #991b1b; }
            .status-used_up { background: #fee2e2; color: #991b1b; }
            .remaining-ok { color: #111827; }
            .remaining-warn { color: #92400e; background: #fef3c7; padding: 2px 6px; border-radius: 6px; }
            .remaining-empty { color: #991b1b; background: #fee2e2; padding: 2px 6px; border-radius: 6px; }
        </style>
@endpush

@section('content')
        <div class="row" style="justify-content: space-between;">
            <div>
                <h1 style="margin: 0;">Student Packages</h1>
            </div>
            <div class="row" style="margin: 0;">
                <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
                <a class="btn btn-secondary" href="{{ url('/private-bookings') }}">Private Bookings</a>
                <a class="btn btn-secondary" href="{{ url('/attendance') }}">Attendance</a>
            </div>
        </div>

        <form method="get" action="{{ url('/student-packages') }}">
            <div class="row">
                <div>
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id">
                        <option value="">All</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected($studentId === $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        @foreach ($availableStatuses as $s)
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
                    <th>Student</th>
                    <th>Package Type</th>
                    <th style="width: 120px;">Total Lessons</th>
                    <th style="width: 120px;">Used Lessons</th>
                    <th style="width: 150px;">Remaining Lessons</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 170px;">Purchased At</th>
                    <th style="width: 170px;">Expires At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studentPackages as $pkg)
                    @php
                        $total = (int) ($pkg->total_units ?? 0);
                        $remaining = (int) ($pkg->remaining_units ?? 0);
                        $used = max(0, $total - $remaining);

                        $statusValue = (string) ($pkg->status ?? '');
                        $statusClass = 'status-'.str_replace('-', '_', $statusValue);

                        $remainingClass = 'remaining-ok';
                        $remainingLabel = (string) $remaining;
                        if ($remaining <= 0) {
                            $remainingClass = 'remaining-empty';
                            $remainingLabel = '0 (Need Renewal)';
                        } elseif ($remaining <= 3) {
                            $remainingClass = 'remaining-warn';
                        }
                    @endphp
                    <tr>
                        <td>{{ $pkg->student?->name }}</td>
                        <td>{{ $pkg->packageType?->name }}</td>
                        <td>{{ $total }}</td>
                        <td>{{ $used }}</td>
                        <td><span class="{{ $remainingClass }}">{{ $remainingLabel }}</span></td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusValue }}</span></td>
                        <td class="muted">{{ $pkg->purchased_at?->format('Y-m-d H:i') ?? $pkg->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="muted">{{ $pkg->expires_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">No student packages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection

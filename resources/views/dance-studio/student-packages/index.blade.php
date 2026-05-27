@extends('layouts.app')

@section('title', 'Student Packages')

@push('styles')
        <style>
            .status-active { background: #d1fae5; color: #065f46; }
            .status-expired { background: #e5e7eb; color: #374151; }
            .status-void { background: #fee2e2; color: #991b1b; }
            .status-used_up { background: #fee2e2; color: #991b1b; }
        </style>
@endpush

@section('content')
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Student Packages</h1>
            </div>
            <div class="page-actions">
                <a class="btn" href="{{ url('/student-packages/create') }}">Create / Renew Package</a>
                <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
                <a class="btn btn-secondary" href="{{ url('/private-bookings') }}">Private Bookings</a>
                <a class="btn btn-secondary" href="{{ url('/attendance') }}">Attendance</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="get" action="{{ url('/student-packages') }}">
            <div class="form-row">
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
                    <button class="btn" type="submit">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table class="table">
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

                            $remainingClass = '';
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
                            <td><span class="badge {{ $remainingClass }}">{{ $remainingLabel }}</span></td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusValue }}</span></td>
                            <td class="muted">{{ $pkg->purchased_at?->format('Y-m-d H:i') ?? $pkg->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="muted">{{ $pkg->expires_at?->format('Y-m-d H:i') }}</td>
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

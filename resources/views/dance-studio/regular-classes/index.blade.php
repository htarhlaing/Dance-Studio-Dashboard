@extends('layouts.app')

@section('title', 'Regular Classes')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Regular Classes</h1>
            <div class="muted">Weekly schedule templates. Used by Availability as regular occupancy.</div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/availability') }}">Back to Availability</a>
            <a class="btn" href="{{ url('/regular-classes/create') }}">Create Regular Class</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Day of Week</th>
                    <th style="width: 120px;">Time</th>
                    <th>Room</th>
                    <th>Teacher</th>
                    <th>Class Type</th>
                    <th>Title</th>
                    <th style="width: 90px;">Active</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
                @endphp
                @forelse ($regularClasses as $rc)
                    @php
                        $dayLabel = $dayNames[(int) $rc->day_of_week] ?? (string) $rc->day_of_week;
                        $start = is_string($rc->start_time) ? substr($rc->start_time, 0, 5) : '';
                        $end = is_string($rc->end_time) ? substr($rc->end_time, 0, 5) : '';
                    @endphp
                    <tr>
                        <td>{{ $dayLabel }}</td>
                        <td>{{ $start && $end ? ($start.'-'.$end) : '' }}</td>
                        <td>{{ $rc->room?->name }}</td>
                        <td>{{ $rc->teacher?->display_name ?? '-' }}</td>
                        <td>{{ $rc->classType?->name }}</td>
                        <td>{{ $rc->title }}</td>
                        <td>
                            <span class="badge {{ $rc->is_active ? 'status-confirmed' : 'status-cancelled' }}">
                                {{ $rc->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-secondary btn-small" href="{{ url('/regular-classes/'.$rc->id.'/edit') }}">Edit</a>
                            <form method="post" action="{{ url('/regular-classes/'.$rc->id) }}" style="display: inline-block;">
                                @csrf
                                @method('delete')
                                <button class="btn btn-secondary btn-small" type="submit">Delete</button>
                            </form>
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

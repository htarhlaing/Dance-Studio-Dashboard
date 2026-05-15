@extends('layouts.app')

@section('title', 'Create Private Booking')

@push('styles')
        <style>
            .row { display: flex; gap: 12px; align-items: end; margin-bottom: 16px; flex-wrap: wrap; }
            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input, select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; min-width: 220px; }
            button { padding: 7px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 6px; cursor: pointer; }
            .muted { color: #6b7280; font-size: 12px; margin-top: 6px; }
            .alert { padding: 10px 12px; border-radius: 8px; margin: 12px 0; }
            .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
            .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
            .field { margin-bottom: 12px; }
        </style>
@endpush

@section('content')
        <h1>Create Private Booking</h1>

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

        <form method="post" action="{{ url('/private-bookings') }}">
            @csrf

            <input type="hidden" name="date" value="{{ old('date', $date) }}">
            <input type="hidden" name="room_id" value="{{ old('room_id', $roomId) }}">
            <input type="hidden" name="start_time" value="{{ old('start_time', $startTime) }}">
            <input type="hidden" name="end_time" value="{{ old('end_time', $endTime) }}">
            <input type="hidden" name="class_type_id" value="{{ old('class_type_id', $classTypeId) }}">
            <input type="hidden" name="duration" value="{{ old('duration', $duration) }}">

            <div class="field">
                <label>Date</label>
                <div>{{ old('date', $date) }}</div>
            </div>
            <div class="field">
                <label>Room</label>
                <div>{{ $room?->name ?? ('#'.old('room_id', $roomId)) }}</div>
            </div>
            <div class="field">
                <label>Start Time</label>
                <div>{{ old('start_time', $startTime) }}</div>
            </div>
            <div class="field">
                <label>End Time</label>
                <div>{{ old('end_time', $endTime) }}</div>
                @if (old('duration', $duration))
                    <div class="muted">Duration: {{ old('duration', $duration) }} minutes</div>
                @endif
            </div>

            <div class="field">
                <label for="student_id">Student</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Select a student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) old('student_id') === $student->id)>{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Select a teacher</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) old('teacher_id') === $teacher->id)>{{ $teacher->display_name ?? ('#'.$teacher->id) }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit">Submit</button>
        </form>
@endsection

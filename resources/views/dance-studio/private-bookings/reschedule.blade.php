@extends('layouts.app')

@section('title', 'Reschedule Booking')

@push('styles')
        <style>
            input, select { min-width: 220px; }
            .muted { color: #6b7280; font-size: 12px; margin-top: 6px; }
            .field { margin-bottom: 12px; }
        </style>
@endpush

@section('content')
        <div class="page-header">
            <div>
                <h1 style="margin: 0;">Reschedule Booking</h1>
            </div>
            <div class="page-actions">
                <a class="btn btn-secondary" href="{{ url('/private-bookings') }}">Back</a>
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

        <form method="post" action="{{ url('/private-bookings/'.$booking->id.'/reschedule') }}">
            @csrf

            <div class="field">
                <label>Student</label>
                <div>{{ $booking->student?->name }}</div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="date">Date</label>
                    <input id="date" name="date" type="date" value="{{ old('date', $booking->start_at?->format('Y-m-d')) }}" required>
                </div>
                <div class="field">
                    <label for="start_time">Start Time</label>
                    <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $booking->start_at?->format('H:i')) }}" required>
                </div>
                <div class="field">
                    <label for="end_time">End Time</label>
                    <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $booking->end_at?->format('H:i')) }}" required>
                </div>
            </div>

            @if ($role === 'teacher')
                <input type="hidden" name="room_id" value="{{ (int) $booking->room_id }}">
                <input type="hidden" name="teacher_id" value="{{ (int) $booking->teacher_id }}">

                <div class="field">
                    <label>Room</label>
                    <div>{{ $booking->room?->name }}</div>
                </div>
                <div class="field">
                    <label>Teacher</label>
                    <div>{{ $booking->teacher?->display_name ?? $booking->teacher_id }}</div>
                </div>
            @else
                <div class="field">
                    <label for="room_id">Room</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Select a room</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((int) old('room_id', (int) $booking->room_id) === $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">Select a teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', (int) $booking->teacher_id) === $teacher->id)>{{ $teacher->display_name ?? ('#'.$teacher->id) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button class="btn" type="submit">Save Reschedule</button>
        </form>
@endsection


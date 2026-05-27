@extends('layouts.app')

@section('title', 'Edit Regular Class')

@push('styles')
    <style>
        .field { margin-bottom: 12px; }
        input, select { min-width: 240px; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Edit Regular Class</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/regular-classes') }}">Back</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ url('/regular-classes/'.$regularClass->id) }}">
        @csrf
        @method('put')

        <div class="form-row">
            <div class="field">
                <label for="day_of_week">Day of Week</label>
                <select id="day_of_week" name="day_of_week" required>
                    @php
                        $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                        $selectedDay = (int) old('day_of_week', (int) $regularClass->day_of_week);
                    @endphp
                    @foreach ($days as $k => $label)
                        <option value="{{ $k }}" @selected($selectedDay === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="start_time">Start Time</label>
                @php
                    $start = old('start_time', is_string($regularClass->start_time) ? substr($regularClass->start_time, 0, 5) : '');
                @endphp
                <input id="start_time" name="start_time" type="time" value="{{ $start }}" required>
            </div>

            <div class="field">
                <label for="end_time">End Time</label>
                @php
                    $end = old('end_time', is_string($regularClass->end_time) ? substr($regularClass->end_time, 0, 5) : '');
                @endphp
                <input id="end_time" name="end_time" type="time" value="{{ $end }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id" required>
                    <option value="">Select a room</option>
                    @php
                        $selectedRoom = (int) old('room_id', (int) $regularClass->room_id);
                    @endphp
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected($selectedRoom === $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">(None)</option>
                    @php
                        $selectedTeacher = old('teacher_id', $regularClass->teacher_id);
                        $selectedTeacher = is_numeric($selectedTeacher) ? (int) $selectedTeacher : null;
                    @endphp
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($selectedTeacher === $teacher->id)>
                            {{ $teacher->display_name ?? ('#'.$teacher->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="class_type_id">Class Type</label>
                <select id="class_type_id" name="class_type_id" required>
                    <option value="">Select a class type</option>
                    @php
                        $selectedClassType = (int) old('class_type_id', (int) $regularClass->class_type_id);
                    @endphp
                    @foreach ($classTypes as $ct)
                        <option value="{{ $ct->id }}" @selected($selectedClassType === $ct->id)>{{ $ct->name }} ({{ $ct->kind }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="120" value="{{ old('title', $regularClass->title) }}" required>
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            Regular Class repeats weekly on the selected day/time. starts_on / ends_on only limit the effective date range.
            <br>
            Regular Class 是每周重复的固定课。starts_on / ends_on 只是用来限制生效日期范围。
        </div>

        <div class="form-row">
            <div class="field">
                <label for="starts_on">Starts On</label>
                <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', $regularClass->starts_on?->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label for="ends_on">Ends On</label>
                <input id="ends_on" name="ends_on" type="date" value="{{ old('ends_on', $regularClass->ends_on?->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label for="is_active">Active</label>
                @php
                    $activeValue = old('is_active', $regularClass->is_active ? '1' : '0');
                @endphp
                <select id="is_active" name="is_active">
                    <option value="1" @selected($activeValue === '1')>Yes</option>
                    <option value="0" @selected($activeValue === '0')>No</option>
                </select>
            </div>
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            If this is a long-term weekly class, leave starts_on and ends_on empty.
            <br>
            如果这是长期固定课，开始日期和结束日期可以留空。
        </div>

        <button class="btn" type="submit">Save</button>
    </form>
@endsection

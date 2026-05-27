@extends('layouts.app')

@section('title', 'Create Regular Class')

@push('styles')
    <style>
        .field { margin-bottom: 12px; }
        input, select { min-width: 240px; }
        .error { color: #991b1b; font-size: 12px; margin-top: 6px; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create Regular Class</h1>
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

    <form method="post" action="{{ url('/regular-classes') }}">
        @csrf

        <div class="form-row">
            <div class="field">
                <label>Weekdays</label>
                <div style="font-size: 12px; color: #6b7280; margin: -4px 0 8px 0;">
                    One Regular Class record represents one weekday schedule. Select multiple weekdays to create multiple weekly rules with the same teacher, room, time, and date range.
                    <br>
                    一条 Regular Class 代表一周中的某一天固定课。如果一个课程一周有多天，可以一次选择多个星期，系统会自动创建多条规则。
                </div>
                @php
                    $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
                    $selectedWeekdays = old('weekdays', [1]);
                    $selectedWeekdays = is_array($selectedWeekdays) ? array_values(array_map('intval', $selectedWeekdays)) : [(int) $selectedWeekdays];
                @endphp
                <div style="display: flex; flex-wrap: wrap; gap: 10px; max-width: 520px;">
                    @foreach ($days as $k => $label)
                        <label style="display: flex; align-items: center; gap: 6px; margin: 0; font-size: 14px; color: #111827;">
                            <input type="checkbox" name="weekdays[]" value="{{ $k }}" @checked(in_array($k, $selectedWeekdays, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="start_time">Start Time</label>
                <input id="start_time" name="start_time" type="time" value="{{ old('start_time', '09:00') }}" required>
            </div>

            <div class="field">
                <label for="end_time">End Time</label>
                <input id="end_time" name="end_time" type="time" value="{{ old('end_time', '10:00') }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id" required>
                    <option value="">Select a room</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected((int) old('room_id') === $room->id)>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="teacher_id">Teacher</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">(None)</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) old('teacher_id') === $teacher->id)>
                            {{ $teacher->display_name ?? ('#'.$teacher->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="class_type_id">Class Type</label>
                <select id="class_type_id" name="class_type_id" required>
                    <option value="">Select a class type</option>
                    @foreach ($classTypes as $ct)
                        <option value="{{ $ct->id }}" @selected((int) old('class_type_id') === $ct->id)>{{ $ct->name }} ({{ $ct->kind }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="title">Title *</label>
            <input id="title" name="title" type="text" maxlength="120" value="{{ old('title') }}" required>
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            Regular Class repeats weekly on the selected day/time. starts_on / ends_on only limit the effective date range.
            <br>
            Regular Class 是每周重复的固定课。starts_on / ends_on 只是用来限制生效日期范围。
        </div>

        <div class="form-row">
            <div class="field">
                <label for="starts_on">Starts On</label>
                <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on') }}">
            </div>
            <div class="field">
                <label for="ends_on">Ends On</label>
                <input id="ends_on" name="ends_on" type="date" value="{{ old('ends_on') }}">
            </div>
            <div class="field">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected(old('is_active', '1') === '1')>Yes</option>
                    <option value="0" @selected(old('is_active') === '0')>No</option>
                </select>
            </div>
        </div>

        <div style="font-size: 12px; color: #6b7280; margin: -6px 0 12px 0;">
            If this is a long-term weekly class, leave starts_on and ends_on empty.
            <br>
            如果这是长期固定课，开始日期和结束日期可以留空。
        </div>

        <button class="btn" type="submit">Submit</button>
    </form>
@endsection

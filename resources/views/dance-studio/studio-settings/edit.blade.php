@extends('layouts.app')

@section('title', 'Studio Settings')

@push('styles')
    <style>
        .card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; max-width: 720px; }
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .field { margin-bottom: 12px; }
        .actions { margin-top: 12px; display: flex; gap: 10px; align-items: center; }
        input { width: 100%; }
        select { width: 100%; }
        @media (max-width: 720px) { .settings-grid { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Studio Settings</h1>
            <div class="muted" style="margin-top: 4px;">Editing: {{ $studio->name }}</div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/studio-settings') }}">Back</a>
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

    <div class="card">
        <form method="post" action="{{ url('/studio-settings') }}">
            @csrf

            <div class="field" id="business-hours">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $studio->name) }}" required>
            </div>

            <div class="settings-grid">
                <div class="field">
                    <label for="business_start_time">Business Start Time</label>
                    <input id="business_start_time" name="business_start_time" type="time" value="{{ old('business_start_time', $studio->business_start_time ? substr((string) $studio->business_start_time, 0, 5) : '') }}">
                </div>
                <div class="field">
                    <label for="business_end_time">Business End Time</label>
                    <input id="business_end_time" name="business_end_time" type="time" value="{{ old('business_end_time', $studio->business_end_time ? substr((string) $studio->business_end_time, 0, 5) : '') }}">
                </div>
            </div>

            <div class="settings-grid">
                <div class="field">
                    <label for="booking_interval_minutes">Booking Interval Minutes</label>
                    <input id="booking_interval_minutes" name="booking_interval_minutes" type="number" min="1" max="1440" value="{{ old('booking_interval_minutes', $studio->booking_interval_minutes) }}">
                </div>
                <div class="field">
                    <label for="private_lesson_duration_minutes">Private Lesson Duration Minutes</label>
                    <input id="private_lesson_duration_minutes" name="private_lesson_duration_minutes" type="number" min="1" max="1440" value="{{ old('private_lesson_duration_minutes', $privateLessonClassType?->default_duration_minutes) }}">
                </div>
            </div>

            <div class="settings-grid">
                <div class="field">
                    <label for="timezone">Timezone</label>
                    <input id="timezone" name="timezone" type="text" value="{{ old('timezone', $studio->timezone) }}" required>
                </div>
                <div class="field">
                    <label for="currency">Currency</label>
                    <input id="currency" name="currency" type="text" maxlength="3" value="{{ old('currency', $studio->currency) }}" required>
                </div>
            </div>

            <div style="margin: 12px 0; border-top: 1px solid #e5e7eb;"></div>

            <h2 id="booking-rules" style="margin: 0 0 10px;">Booking Rules</h2>
            <p class="muted" style="margin-top: 0;">Rules apply to the current studio only.</p>

            <div class="settings-grid">
                <div class="field">
                    <label for="minimum_booking_notice_hours">Minimum booking notice hours</label>
                    <input id="minimum_booking_notice_hours" name="minimum_booking_notice_hours" type="number" min="0" max="8760" value="{{ old('minimum_booking_notice_hours', (int) ($studio->minimum_booking_notice_hours ?? 48)) }}" required>
                    <div class="muted">How many hours in advance a private lesson must be booked.</div>
                </div>
                <div class="field">
                    <label for="minimum_reschedule_notice_hours">Minimum reschedule notice hours</label>
                    <input id="minimum_reschedule_notice_hours" name="minimum_reschedule_notice_hours" type="number" min="0" max="8760" value="{{ old('minimum_reschedule_notice_hours', (int) ($studio->minimum_reschedule_notice_hours ?? 48)) }}" required>
                    <div class="muted">How many hours in advance a private lesson can be rescheduled.</div>
                </div>
            </div>

            <div class="settings-grid">
                <div class="field">
                    <label for="minimum_cancel_notice_hours">Minimum cancel notice hours</label>
                    <input id="minimum_cancel_notice_hours" name="minimum_cancel_notice_hours" type="number" min="0" max="8760" value="{{ old('minimum_cancel_notice_hours', (int) ($studio->minimum_cancel_notice_hours ?? 24)) }}" required>
                    <div class="muted">How many hours in advance a private lesson can be cancelled.</div>
                </div>
                <div class="field">
                    <label style="margin-bottom: 8px;">Options</label>
                    @php
                        $overrideChecked = old('allow_admin_frontdesk_override_notice', (bool) ($studio->allow_admin_frontdesk_override_notice ?? true));
                        $teacherCreateChecked = old('teacher_can_create_booking', (bool) ($studio->teacher_can_create_booking ?? false));
                        $overrideChecked = $overrideChecked === '1' || $overrideChecked === 1 || $overrideChecked === true;
                        $teacherCreateChecked = $teacherCreateChecked === '1' || $teacherCreateChecked === 1 || $teacherCreateChecked === true;
                    @endphp
                    <label style="display: flex; align-items: center; gap: 8px; margin: 0 0 8px 0;">
                        <input type="checkbox" name="allow_admin_frontdesk_override_notice" value="1" @checked($overrideChecked)>
                        Allow admin/front desk override notice limit
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; margin: 0;">
                        <input type="checkbox" name="teacher_can_create_booking" value="1" @checked($teacherCreateChecked)>
                        Allow teachers to create private bookings
                    </label>
                </div>
            </div>

            <div class="actions">
                <button class="btn" type="submit">Save</button>
                @if ($privateLessonClassType === null)
                    <span class="muted">Private Lesson class type not found for this studio.</span>
                @endif
            </div>
        </form>
    </div>
@endsection

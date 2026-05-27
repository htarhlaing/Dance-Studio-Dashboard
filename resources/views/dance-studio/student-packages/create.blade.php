@extends('layouts.app')

@section('title', 'Create / Renew Student Package')

@push('styles')
    <style>
        .field { margin-bottom: 12px; }
        input, select, textarea { min-width: 260px; }
        textarea { width: 100%; max-width: 720px; min-height: 90px; }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create / Renew Package</h1>
            <div class="muted">Renewal is implemented by creating a new student package record.</div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/student-packages') }}">Back</a>
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

    <form method="post" action="{{ url('/student-packages') }}">
        @csrf

        <div class="form-row">
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
                <label for="package_type_id">Package Type</label>
                <select id="package_type_id" name="package_type_id" required>
                    <option value="">Select a package type</option>
                    @foreach ($packageTypes as $pt)
                        <option
                            value="{{ $pt->id }}"
                            data-lessons="{{ (int) $pt->lessons_count }}"
                            data-validity-days="{{ $pt->validity_days !== null ? (int) $pt->validity_days : '' }}"
                            @selected((int) old('package_type_id') === $pt->id)
                        >
                            {{ $pt->name }}
                        </option>
                    @endforeach
                </select>
                <div class="muted">If the package type has lessons_count, it can auto-fill units.</div>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="total_units">Total Units</label>
                <input id="total_units" name="total_units" type="number" min="1" value="{{ old('total_units', '') }}" required>
            </div>

            <div class="field">
                <label for="remaining_units">Remaining Units</label>
                <input id="remaining_units" name="remaining_units" type="number" min="0" value="{{ old('remaining_units', '') }}" required>
                <div class="muted">Default is same as total units.</div>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="purchased_at">Purchased At</label>
                <input id="purchased_at" name="purchased_at" type="datetime-local" value="{{ old('purchased_at', $defaultPurchasedAt) }}" required>
            </div>

            <div class="field">
                <label for="expires_at">Expires At</label>
                <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at') }}">
                <div class="muted">Optional. If validity_days exists, it can auto-fill based on purchased_at.</div>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="status">Status</label>
                @php
                    $statusValue = old('status', 'active');
                @endphp
                <select id="status" name="status">
                    @foreach (['active', 'expired', 'used_up', 'void'] as $s)
                        <option value="{{ $s }}" @selected($statusValue === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
        </div>

        <button class="btn" type="submit">Submit</button>
    </form>

    <script>
        (function () {
            var packageTypeSelect = document.getElementById('package_type_id');
            var totalUnitsInput = document.getElementById('total_units');
            var remainingUnitsInput = document.getElementById('remaining_units');
            var purchasedAtInput = document.getElementById('purchased_at');
            var expiresAtInput = document.getElementById('expires_at');

            function addDaysToLocalDateTime(localDateTime, days) {
                if (!localDateTime || !days) return '';
                var dt = new Date(localDateTime);
                if (isNaN(dt.getTime())) return '';
                dt.setDate(dt.getDate() + days);
                var pad = function (n) { return String(n).padStart(2, '0'); };
                return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()) + 'T' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
            }

            function maybeFillUnits() {
                var opt = packageTypeSelect && packageTypeSelect.options[packageTypeSelect.selectedIndex];
                if (!opt) return;
                var lessons = parseInt(opt.getAttribute('data-lessons') || '', 10);
                if (!lessons || lessons <= 0) return;

                if (!totalUnitsInput.value) totalUnitsInput.value = String(lessons);
                if (!remainingUnitsInput.value) remainingUnitsInput.value = String(lessons);
            }

            function maybeFillExpiry() {
                var opt = packageTypeSelect && packageTypeSelect.options[packageTypeSelect.selectedIndex];
                if (!opt) return;
                var validityDays = parseInt(opt.getAttribute('data-validity-days') || '', 10);
                if (!validityDays || validityDays <= 0) return;
                if (expiresAtInput.value) return;
                var v = addDaysToLocalDateTime(purchasedAtInput.value, validityDays);
                if (v) expiresAtInput.value = v;
            }

            function onPackageTypeChange() {
                maybeFillUnits();
                maybeFillExpiry();
            }

            function onPurchasedAtChange() {
                if (expiresAtInput.value) return;
                maybeFillExpiry();
            }

            if (packageTypeSelect) packageTypeSelect.addEventListener('change', onPackageTypeChange);
            if (purchasedAtInput) purchasedAtInput.addEventListener('change', onPurchasedAtChange);

            onPackageTypeChange();
        })();
    </script>
@endsection


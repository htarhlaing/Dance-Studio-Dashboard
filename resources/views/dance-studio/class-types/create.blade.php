@extends('layouts.app')

@section('title', 'Create Class Type')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create Class Type</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/class-types') }}">Back</a>
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

    <form method="post" action="{{ url('/class-types') }}">
        @csrf
        <div class="form-row">
            <div style="flex: 1;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>
            <div style="width: 220px;">
                <label for="kind">Kind</label>
                <select id="kind" name="kind">
                    @foreach ($kinds as $k)
                        <option value="{{ $k }}" @selected(old('kind', 'both') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 160px;">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected(old('is_active', '1') === '1')>Yes</option>
                    <option value="0" @selected(old('is_active') === '0')>No</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div style="width: 220px;">
                <label for="default_duration_minutes">Default Duration Minutes</label>
                <input id="default_duration_minutes" name="default_duration_minutes" type="number" min="1" value="{{ old('default_duration_minutes') }}">
            </div>
            <div style="width: 220px;">
                <label for="default_deduct_units">Default Deduct Units</label>
                <input id="default_deduct_units" name="default_deduct_units" type="number" min="0" value="{{ old('default_deduct_units', '1') }}">
            </div>
        </div>

        <div style="margin-top: 14px;">
            <button class="btn" type="submit">Submit</button>
        </div>
    </form>
@endsection


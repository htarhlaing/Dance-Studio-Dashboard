@extends('layouts.app')

@section('title', 'Edit Package Type')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Edit Package Type</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/package-types') }}">Back</a>
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

    <form method="post" action="{{ url('/package-types/'.$packageType->id) }}">
        @csrf
        @method('put')

        <div class="form-row">
            <div style="flex: 1;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $packageType->name) }}" required>
            </div>
            <div style="width: 160px;">
                <label for="lessons_count">Lessons Count</label>
                <input id="lessons_count" name="lessons_count" type="number" min="1" value="{{ old('lessons_count', $packageType->lessons_count) }}" required>
            </div>
            <div style="width: 160px;">
                <label for="validity_days">Validity Days</label>
                <input id="validity_days" name="validity_days" type="number" min="1" value="{{ old('validity_days', $packageType->validity_days) }}">
            </div>
        </div>

        <div class="form-row">
            <div style="width: 200px;">
                <label for="price">Price</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $packageType->price) }}" required>
            </div>
            <div style="width: 160px;">
                <label for="currency">Currency</label>
                <input id="currency" name="currency" type="text" maxlength="3" value="{{ old('currency', $packageType->currency) }}" required>
            </div>
            <div style="width: 160px;">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected((string) old('is_active', (int) $packageType->is_active) === '1')>Yes</option>
                    <option value="0" @selected((string) old('is_active', (int) $packageType->is_active) === '0')>No</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 14px;">
            <button class="btn" type="submit">Submit</button>
        </div>
    </form>
@endsection


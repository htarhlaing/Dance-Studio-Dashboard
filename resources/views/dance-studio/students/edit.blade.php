@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Edit Student</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/students') }}">Back</a>
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

    <form method="post" action="{{ url('/students/'.$student->id) }}">
        @csrf
        @method('put')

        <div class="form-row">
            <div style="flex: 1;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" required>
            </div>
            <div style="width: 240px;">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone) }}">
            </div>
            <div style="width: 160px;">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected((string) old('is_active', (int) $student->is_active) === '1')>Yes</option>
                    <option value="0" @selected((string) old('is_active', (int) $student->is_active) === '0')>No</option>
                </select>
            </div>
        </div>

        <div>
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4">{{ old('notes', $student->notes) }}</textarea>
        </div>

        <div style="margin-top: 14px;">
            <button class="btn" type="submit">Submit</button>
        </div>
    </form>
@endsection


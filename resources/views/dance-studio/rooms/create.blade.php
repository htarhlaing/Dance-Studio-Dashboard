@extends('layouts.app')

@section('title', 'Create Room')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create Room</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/rooms') }}">Back</a>
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

    <form method="post" action="{{ url('/rooms') }}">
        @csrf
        <div class="form-row">
            <div style="flex: 1;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>
            <div style="width: 180px;">
                <label for="capacity">Capacity</label>
                <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity') }}">
            </div>
            <div style="width: 160px;">
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="1" @selected(old('is_active', '1') === '1')>Yes</option>
                    <option value="0" @selected(old('is_active') === '0')>No</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 14px;">
            <button class="btn" type="submit">Submit</button>
        </div>
    </form>
@endsection


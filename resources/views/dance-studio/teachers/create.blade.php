@extends('layouts.app')

@section('title', 'Create Teacher')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Create Teacher</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ url('/teachers') }}">Back</a>
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

    <form method="post" action="{{ url('/teachers') }}">
        @csrf
        <div class="form-row">
            <div style="flex: 1;">
                <label for="display_name">Display Name</label>
                <input id="display_name" name="display_name" type="text" value="{{ old('display_name') }}" required>
            </div>
            <div style="width: 320px;">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id" required>
                    <option value="">Select a user</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>
                            {{ $user->name }}@if ($user->email) ({{ $user->email }}) @endif
                        </option>
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

        <div>
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4">{{ old('bio') }}</textarea>
        </div>

        <div style="margin-top: 14px;">
            <button class="btn" type="submit">Submit</button>
        </div>
    </form>
@endsection


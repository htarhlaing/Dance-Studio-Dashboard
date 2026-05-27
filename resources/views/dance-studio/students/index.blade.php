@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Students</h1>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ url('/students/create') }}">Create Student</a>
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

    <form method="get" action="{{ url('/students') }}">
        <div class="form-row">
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ $name }}">
            </div>
            <div>
                <label for="is_active">Active</label>
                <select id="is_active" name="is_active">
                    <option value="">All</option>
                    <option value="1" @selected($isActive === true)>Active</option>
                    <option value="0" @selected($isActive === false)>Inactive</option>
                </select>
            </div>
            <div>
                <button class="btn" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="width: 160px;">Phone</th>
                    <th>Notes</th>
                    <th style="width: 90px;">Active</th>
                    <th style="width: 170px;">Created At</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td class="muted">{{ $student->phone }}</td>
                        <td class="muted">{{ $student->notes }}</td>
                        <td>
                            <span class="badge {{ $student->is_active ? 'status-confirmed' : 'status-cancelled' }}">
                                {{ $student->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="muted">{{ $student->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a class="btn btn-secondary btn-small" href="{{ url('/students/'.$student->id.'/edit') }}">Edit</a>
                            <form method="post" action="{{ url('/students/'.$student->id) }}" style="display: inline-block;">
                                @csrf
                                @method('delete')
                                <button class="btn btn-secondary btn-small" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Class Types')

@section('content')
    <div class="page-header">
        <div>
            <h1 style="margin: 0;">Class Types</h1>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ url('/class-types/create') }}">Create Class Type</a>
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

    <form method="get" action="{{ url('/class-types') }}">
        <div class="form-row">
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ $name }}">
            </div>
            <div>
                <label for="kind">Kind</label>
                <select id="kind" name="kind">
                    <option value="">All</option>
                    @foreach ($kinds as $k)
                        <option value="{{ $k }}" @selected($kind === $k)>{{ $k }}</option>
                    @endforeach
                </select>
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
                    <th style="width: 110px;">Kind</th>
                    <th style="width: 160px;">Default Duration</th>
                    <th style="width: 170px;">Default Deduct</th>
                    <th style="width: 90px;">Active</th>
                    <th style="width: 170px;">Created At</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classTypes as $classType)
                    <tr>
                        <td>{{ $classType->name }}</td>
                        <td class="muted">{{ $classType->kind }}</td>
                        <td class="muted">{{ $classType->default_duration_minutes }}</td>
                        <td class="muted">{{ $classType->default_deduct_units }}</td>
                        <td>
                            <span class="badge {{ $classType->is_active ? 'status-confirmed' : 'status-cancelled' }}">
                                {{ $classType->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="muted">{{ $classType->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a class="btn btn-secondary btn-small" href="{{ url('/class-types/'.$classType->id.'/edit') }}">Edit</a>
                            <form method="post" action="{{ url('/class-types/'.$classType->id) }}" style="display: inline-block;">
                                @csrf
                                @method('delete')
                                <button class="btn btn-secondary btn-small" type="submit">Deactivate</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection


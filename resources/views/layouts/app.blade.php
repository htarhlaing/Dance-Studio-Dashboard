<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'DanceApp')</title>
        <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 0; background: #f8fafc; color: #111827; }
            h1 { margin: 0 0 8px; }
            h2 { margin: 0 0 10px; }

            .nav { background: #ffffff; border-bottom: 1px solid #e5e7eb; }
            .nav-inner { display: flex; gap: 10px; padding: 12px 24px; flex-wrap: wrap; align-items: center; }
            .nav-links { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
            .nav-groups { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; width: 100%; }
            .nav-group { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
            .nav-group-title { font-size: 12px; color: #6b7280; margin-right: 6px; }
            .nav-right { margin-left: auto; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
            .nav a { text-decoration: none; color: #111827; padding: 7px 10px; border-radius: 8px; border: 1px solid transparent; font-size: 14px; line-height: 1.2; }
            .nav a:hover { background: #f3f4f6; }
            .nav a.active { background: #111827; color: #ffffff; }

            .container { padding: 24px; }
            .muted { color: #6b7280; font-size: 12px; }

            .page-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
            .page-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
            .form-row { display: flex; gap: 12px; align-items: end; flex-wrap: wrap; margin-bottom: 16px; }

            label { display: block; font-size: 12px; color: #374151; margin-bottom: 6px; }
            input, select { padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }

            .btn { padding: 8px 12px; border: 1px solid #111827; background: #111827; color: #fff; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; line-height: 1.2; transition: background 120ms ease, border-color 120ms ease, color 120ms ease; }
            .btn:hover { background: #0f172a; border-color: #0f172a; }
            .btn:focus-visible { outline: 2px solid #93c5fd; outline-offset: 2px; }
            .btn-secondary { background: #fff; color: #111827; }
            .btn-secondary:hover { background: #f3f4f6; border-color: #111827; color: #111827; }
            .btn-small { padding: 4px 8px; font-size: 12px; border-radius: 6px; }
            button.btn { appearance: none; }
            .btn-book { background: #16a34a; border-color: #16a34a; }
            .btn-book:hover { background: #15803d; border-color: #15803d; }

            .table { border-collapse: collapse; width: 100%; background: #ffffff; }
            .table th, .table td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; vertical-align: top; }
            .table th { background: #f3f4f6; }
            .table-wrap { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
            .table-wrap .table { border: 0; }
            .table-wrap .table th, .table-wrap .table td { border-left: 0; border-right: 0; }
            .empty-state { color: #6b7280; font-size: 12px; padding: 12px 10px; }

            .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; color: #111827; background: #e5e7eb; }
            .alert { padding: 10px 12px; border-radius: 8px; margin: 12px 0; }
            .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
            .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

            .status-pending { background: #fef3c7; color: #92400e; }
            .status-confirmed { background: #dbeafe; color: #1e40af; }
            .status-completed { background: #d1fae5; color: #065f46; }
            .status-cancelled { background: #e5e7eb; color: #374151; }
            .status-available { background: #dcfce7; color: #166534; }
            .status-regular { background: #dbeafe; color: #1e40af; }
            .status-private_booked { background: #ffedd5; color: #9a3412; }
            .remaining-warn { background: #fef3c7; color: #92400e; }
            .remaining-empty { background: #fee2e2; color: #991b1b; }

            .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 12px 0 18px; }
            .stat-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
            .stat-label { color: #6b7280; font-size: 12px; margin: 0 0 6px; }
            .stat-value { font-size: 28px; font-weight: 800; margin: 0; color: #111827; letter-spacing: -0.02em; }
            @media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            @media (max-width: 520px) { .stats-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); } }
        </style>
        @stack('styles')
    </head>
    <body>
        @auth
            @php
                $role = (string) (auth()->user()?->role ?? '');
                $isAdmin = $role === 'admin';
                $isFrontDesk = $role === 'front_desk';
                $isTeacher = $role === 'teacher';
            @endphp
            <div class="nav">
                <div class="nav-inner">
                    <div class="nav-groups">
                        <div class="nav-group">
                            <span class="nav-group-title">Main</span>
                            <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ url('/availability') }}" class="{{ request()->is('availability') ? 'active' : '' }}">Availability</a>
                            @if ($isAdmin || $isFrontDesk)
                                <a href="{{ url('/weekly-schedule') }}" class="{{ request()->is('weekly-schedule*') ? 'active' : '' }}">Weekly Schedule</a>
                                <a href="{{ url('/regular-classes') }}" class="{{ request()->is('regular-classes*') ? 'active' : '' }}">Regular Classes</a>
                            @endif
                            <a href="{{ url('/private-bookings') }}" class="{{ request()->is('private-bookings*') ? 'active' : '' }}">Private Bookings</a>
                            <a href="{{ url('/attendance') }}" class="{{ request()->is('attendance*') ? 'active' : '' }}">Attendance</a>
                        </div>

                        <div class="nav-group">
                            <span class="nav-group-title">Management</span>
                            @if ($isAdmin || $isFrontDesk)
                                <a href="{{ url('/students') }}" class="{{ request()->is('students*') ? 'active' : '' }}">Students</a>
                                <a href="{{ url('/teachers') }}" class="{{ request()->is('teachers*') ? 'active' : '' }}">Teachers</a>
                                <a href="{{ url('/rooms') }}" class="{{ request()->is('rooms*') ? 'active' : '' }}">Rooms</a>
                                <a href="{{ url('/student-packages') }}" class="{{ request()->is('student-packages*') ? 'active' : '' }}">Student Packages</a>
                            @endif
                            @if ($isAdmin)
                                <a href="{{ url('/package-types') }}" class="{{ request()->is('package-types*') ? 'active' : '' }}">Package Types</a>
                                <a href="{{ url('/class-types') }}" class="{{ request()->is('class-types*') ? 'active' : '' }}">Class Types</a>
                                <a href="{{ url('/studio-settings') }}" class="{{ request()->is('studio-settings*') ? 'active' : '' }}">Studio Settings</a>
                            @endif
                        </div>
                    </div>

                    <div class="nav-right">
                        <span class="muted">
                            {{ auth()->user()->name ?: auth()->user()->email }}
                            ({{ $role }})
                        </span>
                        <form method="post" action="{{ url('/logout') }}" style="display: inline-block;">
                            @csrf
                            <button class="btn btn-secondary btn-small" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>

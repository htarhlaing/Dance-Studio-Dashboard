<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'DanceApp')</title>
        <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 0; background: #f8fafc; }
            .nav { background: #ffffff; border-bottom: 1px solid #e5e7eb; }
            .nav-inner { display: flex; gap: 12px; padding: 12px 24px; flex-wrap: wrap; align-items: center; }
            .nav a { text-decoration: none; color: #111827; padding: 6px 10px; border-radius: 8px; }
            .nav a.active { background: #111827; color: #ffffff; }
            .container { padding: 24px; }
        </style>
        @stack('styles')
    </head>
    <body>
        <div class="nav">
            <div class="nav-inner">
                <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ url('/availability') }}" class="{{ request()->is('availability') ? 'active' : '' }}">Availability</a>
                <a href="{{ url('/private-bookings') }}" class="{{ request()->is('private-bookings*') ? 'active' : '' }}">Private Bookings</a>
                <a href="{{ url('/attendance') }}" class="{{ request()->is('attendance*') ? 'active' : '' }}">Attendance</a>
                <a href="{{ url('/student-packages') }}" class="{{ request()->is('student-packages*') ? 'active' : '' }}">Student Packages</a>
            </div>
        </div>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>

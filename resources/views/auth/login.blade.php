@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div style="max-width: 420px; margin: 40px auto;">
        <h1>Login</h1>
        <div class="muted">Use your email and password.</div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ url('/login') }}">
            @csrf
            <div style="margin-top: 14px;">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required style="width: 100%;">
            </div>

            <div style="margin-top: 14px;">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required style="width: 100%;">
            </div>

            <div style="margin-top: 16px;">
                <button class="btn" type="submit">Login</button>
            </div>
        </form>
    </div>
@endsection


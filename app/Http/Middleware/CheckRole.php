<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $role = (string) (Auth::user()?->role ?? '');
        if (! in_array($role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}


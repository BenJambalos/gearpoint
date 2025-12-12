<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Accepts a pipe-separated or comma-separated list of allowed roles.
     */
    public function handle(Request $request, Closure $next, $roles = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (empty($roles)) {
            return $next($request);
        }

        // support both pipe and comma as delimiter
        $allowed = preg_split('/[|,]/', $roles);
        if (!in_array($user->role, $allowed)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}

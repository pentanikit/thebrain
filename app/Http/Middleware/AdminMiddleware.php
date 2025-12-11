<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Not logged in
        if (!Auth::check()) {
            return redirect()
                ->route('loginform')
                ->with('error', 'Please login as admin to access this page.');
        }

        // Logged in but role is not admin
        if (Auth::user()->role !== 'admin') {
            // Optional: logout them if you want
            // Auth::logout();

            return redirect()
                ->route('loginform')
                ->with('error', 'You are not authorized to access this page.');
        }

        // User is admin, proceed
        return $next($request);
    }
}

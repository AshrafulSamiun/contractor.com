<?php

namespace App\Http\Middleware;

use App\Services\AccountSecurityActivityTracker;
use Closure;
use Illuminate\Http\Request;

class TrackAccountSecurityActivity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) app(AccountSecurityActivityTracker::class)->touch($request->user(), $request);
        return $next($request);
    }
}

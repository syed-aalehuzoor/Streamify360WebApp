<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->user_status !== 'active') {
                return response()->view('banned');
            }
            
            if (Auth::user()->userplan_expiry && Auth::user()->userplan_expiry < today()) {
                Auth::user()->userplan = config('system.plans')[0];
                Auth::user()->save(); // Persist the change if necessary
            }
        }
        
        return $next($request);
    }
    
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $plan): Response
    {
            
        // Check if the user is authenticated
        if (Auth::check()) {
            $userPlan = Auth::user()->userplan;
            // Now check if the authenticated user is an admin
            if ($userPlan === $plan || ($plan === 'premium' && $userPlan === 'enterprise')) {
                return $next($request);
            }
        }        
        abort(404);
    }
}
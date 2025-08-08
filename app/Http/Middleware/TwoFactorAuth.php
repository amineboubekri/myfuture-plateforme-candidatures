<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Only apply 2FA to students
        if (!$user || $user->role !== 'student') {
            return $next($request);
        }
        
        // Allow access to certain routes without 2FA verification
        $allowedRoutes = [
            '2fa/setup',
            '2fa/enable',
            '2fa/disable', 
            '2fa/reset',
            'change-password',
            'logout'
        ];
        
        $currentRoute = $request->path();
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_contains($currentRoute, $allowedRoute)) {
                return $next($request);
            }
        }
        
        // Check 2FA only for students
        if ($user->google2fa_enabled) {
            if (!session('2fa_verified')) {
                return redirect()->route('2fa.verify');
            }
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Accès refusé');
        }
        
        // Check if student user is approved
        if ($role === 'student' && !Auth::user()->is_approved) {
            Auth::logout();
            return redirect('/pending-approval')
                ->with('message', 'Votre compte est en attente d\'approbation par un administrateur.');
        }
        
        return $next($request);
    }
}

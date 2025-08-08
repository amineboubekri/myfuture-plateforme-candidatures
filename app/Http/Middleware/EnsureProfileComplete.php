<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Only check for students
        if ($user && $user->role === 'student') {
            // Update profile completion status based on current data
            $user->updateProfileCompletionStatus();
            
            // Check if profile is complete
            if (!$user->isProfileComplete()) {
                // Allow access to profile setup routes and logout
                $allowedRoutes = [
                    'student/profile/setup',
                    'student/profile/update', 
                    'logout',
                    'change-password'
                ];
                
                $currentPath = $request->path();
                
                // Debug log
                \Log::info('Profile incomplete middleware triggered', [
                    'user_id' => $user->id,
                    'current_path' => $currentPath,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'date_of_birth' => $user->date_of_birth,
                    'profile_completed' => $user->profile_completed
                ]);
                
                if (!in_array($currentPath, $allowedRoutes)) {
                    return redirect()->route('student.profile.setup')
                        ->with('warning', 'Veuillez compléter votre profil avant de continuer.');
                }
            }
        }
        
        return $next($request);
    }
}

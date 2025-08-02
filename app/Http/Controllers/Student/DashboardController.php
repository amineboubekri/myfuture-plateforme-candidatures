<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $application = $user->applications()->orderByDesc('created_at')->first();
        if ($application) {
            $application->refresh();
            $application->load(['documents', 'steps']);
        }
        // Calculate progress based on application status
        $progress = $this->calculateProgressFromStatus($application ? $application->status : null);
        
        // Alternative: Calculate progress based on completed steps (keep this as fallback)
        $totalSteps = $application ? $application->steps->count() : 0;
        $completedSteps = $application ? $application->steps->where('status', 'completed')->count() : 0;
        if ($totalSteps > 0) {
            $stepProgress = round(($completedSteps / $totalSteps) * 100);
            // Use the higher of the two progress calculations
            $progress = max($progress, $stepProgress);
        }
        $missingDocuments = $application ? $application->documents->where('status', 'pending') : collect();
        $notifications = $user->notifications()->whereNull('read_at')->latest()->take(5)->get();
        return view('student.dashboard', [
            'progress' => $progress,
            'application_status' => $application ? $application->status : null,
            'missing_documents' => $missingDocuments,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Calculate progress percentage based on application status
     */
    private function calculateProgressFromStatus($status)
    {
        if (!$status) {
            return 0;
        }

        switch ($status) {
            case 'pending':
                return 20; // Application submitted, waiting for review
            case 'in_progress':
                return 60; // Application is being processed/reviewed
            case 'completed':
                return 80; // Application completed, awaiting final decision
            case 'approved':
                return 100; // Application approved - success!
            case 'rejected':
                return 100; // Application rejected - process complete (but unsuccessful)
            default:
                return 0;
        }
    }
}

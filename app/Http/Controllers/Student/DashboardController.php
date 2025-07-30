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
        $progress = 0;
        $totalSteps = $application ? $application->steps->count() : 0;
        $completedSteps = $application ? $application->steps->where('status', 'completed')->count() : 0;
        if ($totalSteps > 0) {
            $progress = round(($completedSteps / $totalSteps) * 100);
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
}

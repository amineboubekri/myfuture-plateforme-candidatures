<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Statistiques globales
        $totalApplications = \App\Models\Application::count();
        $pendingApplications = \App\Models\Application::where('status', 'pending')->count();
        $inProgressApplications = \App\Models\Application::where('status', 'in_progress')->count();
        $completedApplications = \App\Models\Application::where('status', 'completed')->count();
        $urgentApplications = \App\Models\Application::where('priority_level', 'high')->where('status', '!=', 'completed')->get();
        // KPI par pays
        $applicationsByCountry = \App\Models\Application::selectRaw('country, count(*) as total')
            ->groupBy('country')->get();
        // KPI par statut
        $applicationsByStatus = \App\Models\Application::selectRaw('status, count(*) as total')
            ->groupBy('status')->get();
        // Données pour graphiques (Chart.js)
        $chartData = [
            'labels' => $applicationsByStatus->pluck('status'),
            'data' => $applicationsByStatus->pluck('total'),
        ];
        return view('admin.dashboard', [
            'total_applications' => $totalApplications,
            'pending_applications' => $pendingApplications,
            'in_progress_applications' => $inProgressApplications,
            'completed_applications' => $completedApplications,
            'urgent_applications' => $urgentApplications,
            'applications_by_country' => $applicationsByCountry,
            'applications_by_status' => $applicationsByStatus,
            'chart_data' => $chartData,
        ]);
    }
}

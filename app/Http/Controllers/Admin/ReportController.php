<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with('user');
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        $applications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Handle CSV export
        if ($request->get('export') === 'excel') {
            $filteredApplications = $query->get(); // Get filtered data without pagination for export
            return $this->exportToCsv($filteredApplications);
        }
        
        // Handle PDF export
        if ($request->get('export') === 'pdf') {
            $applications = $query->get(); // Get filtered data without pagination for export
            $pdf = Pdf::loadView('admin.reports.pdf', compact('applications'));
            return $pdf->download('applications_report.pdf');
        }

        return view('admin.reports', [
            'applications' => $applications
        ]);
    }

    private function exportToCsv($applications)
    {
        $filename = 'applications_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 support in Excel
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Nom',
                'Email', 
                'Pays',
                'Programme',
                'Statut',
                'Date de création',
                'Date de mise à jour'
            ], ';'); // Using semicolon as delimiter for better Excel compatibility
            
            // Data rows
            foreach ($applications as $application) {
                fputcsv($file, [
                    $application->id,
                    $application->user->name ?? 'N/A',
                    $application->user->email ?? 'N/A',
                    $application->country,
                    $application->program_name,
                    $application->status,
                    $application->created_at->format('Y-m-d H:i:s'),
                    $application->updated_at->format('Y-m-d H:i:s')
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}

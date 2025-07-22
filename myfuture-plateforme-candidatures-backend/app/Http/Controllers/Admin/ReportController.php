<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Response;

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

        // Export Excel/PDF (squelette, à compléter avec package type maatwebsite/excel ou dompdf)
        if ($request->get('export') === 'excel') {
            // TODO: Générer un export Excel
            return Response::make('Export Excel non implémenté', 501);
        }
        if ($request->get('export') === 'pdf') {
            // TODO: Générer un export PDF
            return Response::make('Export PDF non implémenté', 501);
        }

        return view('admin.reports', [
            'applications' => $applications
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Application::with('user');
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        $applications = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.applications', [
            'applications' => $applications
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected,approved',
        ]);
        $application = \App\Models\Application::findOrFail($id);
        $application->status = $request->status;
        $application->save();
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}

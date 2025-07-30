<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequiredDocument;
use App\Models\Document;

class ApplicationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $application = $user->applications()->with(['steps', 'documents'])->latest()->first();
        return view('student.application', [
            'application' => $application
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        
        // Check if user already has an application
        $existingApplication = $user->applications()->latest()->first();
        if ($existingApplication) {
            return redirect('/student/application')->with('error', 'Vous avez déjà une candidature en cours.');
        }
        
        // Get all required documents and check what user has uploaded
        $requiredDocuments = RequiredDocument::getActiveRequiredDocuments();
        $userDocuments = [];
        
        // Since no application exists yet, no documents are uploaded
        $missingDocuments = $requiredDocuments;
        
        return view('student.application_create', [
            'required_documents' => $requiredDocuments,
            'missing_documents' => $missingDocuments,
            'user_documents' => $userDocuments,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'university_name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'priority_level' => 'required|in:low,medium,high',
            'estimated_completion_date' => 'nullable|date',
        ]);
        $application = $user->applications()->create([
            'university_name' => $data['university_name'],
            'country' => $data['country'],
            'program' => $data['program'],
            'priority_level' => $data['priority_level'],
            'estimated_completion_date' => $data['estimated_completion_date'],
            'status' => 'pending',
        ]);
        return redirect()->route('student.documents')->with('success', 'Candidature créée. Vous pouvez maintenant uploader vos documents.');
    }
    
    public function submitApplication(Request $request)
    {
        $user = $request->user();
        $application = $user->applications()->latest()->first();
        
        if (!$application) {
            return redirect()->back()->with('error', 'Aucune candidature trouvée.');
        }
        
        // Check if all required documents are uploaded
        $requiredDocuments = RequiredDocument::getActiveRequiredDocuments();
        $submittedDocumentTypes = $application->documents->pluck('document_type')->toArray();
        $missingDocuments = $requiredDocuments->whereNotIn('document_type', $submittedDocumentTypes);
        
        if ($missingDocuments->isNotEmpty()) {
            return redirect()->back()->withErrors(['submit_error' => 'Tous les documents requis doivent être uploadés avant de soumettre la candidature.']);
        }
        
        // Change application status to in_progress
        $application->status = 'in_progress';
        $application->save();
        
        return redirect()->route('student.documents')->with('success', 'Candidature soumise avec succès. Elle est maintenant en cours d\'examen par nos équipes.');
    }
}

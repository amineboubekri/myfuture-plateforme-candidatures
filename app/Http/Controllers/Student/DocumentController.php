<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequiredDocument;
use App\Models\Document;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $user = $request->user();
        $application = $user->applications()->latest()->first();
        if (!$application) {
            return redirect()->back()->withErrors(['upload_error' => 'Aucune candidature trouvée.']);
        }
        
        // Check if document type already exists for this application
        $existingDocument = $application->documents()->where('document_type', $request->document_type)->first();
        if ($existingDocument) {
            return redirect()->back()->withErrors(['upload_error' => 'Ce type de document a déjà été uploadé. Supprimez l\'ancien document avant d\'en uploader un nouveau.']);
        }
        try {
            $fileService = new \App\Services\FileUploadService();
            $filePath = $fileService->upload($request->file('file'));
            
            if (!$filePath) {
                \Log::error('File upload failed - FileUploadService returned false', [
                    'user_id' => $user->id,
                    'document_type' => $request->document_type,
                    'file_name' => $request->file('file')->getClientOriginalName()
                ]);
                return redirect()->back()->withErrors(['upload_error' => 'Le téléchargement du fichier a échoué. Veuillez vérifier le format et la taille du fichier.']);
            }
        } catch (\Exception $e) {
            \Log::error('File upload exception: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'exception' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['upload_error' => 'Une erreur technique est survenue lors du téléchargement. Veuillez réessayer.']);
        }
        
        // For debugging purposes - you can uncomment this line to see what $filePath contains
        // dd('File path returned:', $filePath);
        
        $document = $application->documents()->create([
            'document_type' => $request->document_type,
            'file_path' => $filePath,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);
        return redirect()->route('student.documents')->with('success', 'Document uploadé avec succès.');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $application = $user->applications()->latest()->first();
        $documents = $application ? $application->documents : collect();
        
        // Get required documents and check completion status
        $requiredDocuments = RequiredDocument::getActiveRequiredDocuments();
        $submittedDocumentTypes = $documents->pluck('document_type')->toArray();
        $missingDocuments = $requiredDocuments->whereNotIn('document_type', $submittedDocumentTypes);
        $canSubmitApplication = $missingDocuments->isEmpty();
        
        return view('student.documents', [
            'documents' => $documents,
            'application' => $application,
            'required_documents' => $requiredDocuments,
            'missing_documents' => $missingDocuments,
            'can_submit_application' => $canSubmitApplication
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Document::with('application.user');
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to show pending documents first
            $query->orderBy('status', 'asc'); // pending comes before approved/rejected alphabetically
        }
        
        // Filter by document type if provided
        if ($request->filled('document_type')) {
            $query->where('document_type', 'like', '%' . $request->document_type . '%');
        }
        
        $documents = $query->orderBy('uploaded_at', 'desc')->paginate(15);
        
        return view('admin.documents', [
            'documents' => $documents
        ]);
    }

    public function validateDocument(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);
        $document = \App\Models\Document::findOrFail($id);
        $document->status = $request->status;
        $document->comments = $request->comments;
        $document->validated_at = now();
        $document->validated_by = $request->user()->id;
        $document->save();
        // Générer une notification à l'étudiant
        $student = $document->application->user;
        \App\Models\Notification::create([
            'user_id' => $student->id,
            'type' => 'document',
            'title' => 'Document ' . ($request->status == 'approved' ? 'validé' : 'rejeté'),
            'message' => 'Votre document "' . $document->document_type . '" a été ' . ($request->status == 'approved' ? 'validé' : 'rejeté') . '.',
        ]);
        return redirect()->back()->with('success', 'Document mis à jour.');
    }

    public function viewDocument($id)
    {
        $document = \App\Models\Document::with('application.user')->findOrFail($id);
        
        return view('admin.document_view', compact('document'));
    }

    public function downloadDocument($id)
    {
        $document = \App\Models\Document::findOrFail($id);
        
        $filePath = $this->findDocumentFile($document);
        
        if (!$filePath) {
            return redirect()->back()->with('error', 'Fichier introuvable.');
        }
        
        return response()->download($filePath, basename($document->file_path));
    }

    public function serveDocument($id)
    {
        $document = \App\Models\Document::findOrFail($id);
        
        $filePath = $this->findDocumentFile($document);
        
        if (!$filePath) {
            abort(404, 'File not found');
        }
        
        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $mimeType = match($fileExtension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream'
        };
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($document->file_path) . '"'
        ]);
    }

    /**
     * Find the actual file path for a document, handling both old and new path formats
     */
    private function findDocumentFile($document)
    {
        // List of possible file locations to check
        $possiblePaths = [
            // New format: storage/app/public/documents/file.pdf
            storage_path('app/public/' . $document->file_path),
            
            // Old format: public/uploads/file.pdf
            public_path($document->file_path),
            
            // Fallback: try to find file in documents folder with just filename
            storage_path('app/public/documents/' . basename($document->file_path)),
            
            // Another fallback: try uploads folder with just filename  
            public_path('uploads/' . basename($document->file_path))
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                return $path;
            }
        }
        
        return null;
    }
}

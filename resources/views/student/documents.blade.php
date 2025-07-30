@extends('layouts.app')
@section('title', 'Mes Documents')
@section('content')
<h2>Mes documents</h2>

@if(isset($required_documents) && $required_documents->isNotEmpty())
<div class="card mb-3">
    <div class="card-header">Documents requis pour la candidature</div>
    <div class="card-body">
        @if(isset($missing_documents) && $missing_documents->isNotEmpty())
            <div class="alert alert-warning">
                <strong>Documents manquants :</strong> Vous devez uploader tous les documents requis pour soumettre votre candidature.
            </div>
        @else
            <div class="alert alert-success">
                <strong>Félicitations !</strong> Tous les documents requis ont été uploadés. Votre candidature peut être soumise.
            </div>
            @if(isset($application) && $application->status == 'pending')
                <form method="POST" action="/student/application/submit" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg">Soumettre la Candidature</button>
                </form>
            @endif
        @endif
        
        <div class="row">
            @foreach($required_documents as $reqDoc)
                @php
                    $uploaded = $documents->where('document_type', $reqDoc->document_type)->first();
                @endphp
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                        @if($uploaded)
                            <span class="badge bg-success me-2">✓</span>
                        @else
                            <span class="badge bg-danger me-2">✗</span>
                        @endif
                        <div>
                            <strong>{{ $reqDoc->document_type }}</strong><br>
                            <small class="text-muted">{{ $reqDoc->description }}</small>
                            @if($uploaded)
                                <br><span class="badge bg-{{ $uploaded->status == 'approved' ? 'success' : ($uploaded->status == 'pending' ? 'warning' : 'danger') }}">{{ $uploaded->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
<div class="card mb-3">
    <div class="card-header">Uploader un document</div>
    <div class="card-body">
        <form method="POST" action="/student/documents/upload" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="document_type" class="form-label">Type de document</label>
                @if(isset($required_documents) && $required_documents->isNotEmpty())
                    <select class="form-select" id="document_type" name="document_type" required>
                        <option value="">Sélectionnez un type de document</option>
                        @foreach($required_documents as $reqDoc)
                            <option value="{{ $reqDoc->document_type }}">{{ $reqDoc->document_type }} - {{ $reqDoc->description }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" id="document_type" name="document_type" required>
                @endif
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Fichier (PDF, JPG, PNG)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <button type="submit" class="btn btn-primary">Uploader</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">Liste de mes documents</div>
    <div class="card-body">
        @if(count($documents))
            <ul>
                @foreach($documents as $doc)
                    <li>{{ $doc->document_type }} - <span class="badge bg-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'pending' ? 'warning' : 'danger') }}">{{ $doc->status }}</span></li>
                @endforeach
            </ul>
        @else
            <p>Aucun document.</p>
        @endif
    </div>
</div>
@endsection 
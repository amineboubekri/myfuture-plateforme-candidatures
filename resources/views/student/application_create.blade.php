@extends('layouts.app')
@section('title', 'Nouvelle candidature')
@section('content')
<h2>Créer une nouvelle candidature</h2>

@if(isset($required_documents) && $required_documents->isNotEmpty())
<div class="alert alert-info mb-4">
    <h5><i class="fas fa-info-circle"></i> Documents requis</h5>
    <p>Après avoir créé votre candidature, vous devrez uploader les documents suivants :</p>
    <ul>
        @foreach($required_documents as $doc)
            <li><strong>{{ $doc->document_type }}</strong> - {{ $doc->description }}</li>
        @endforeach
    </ul>
    <p class="mb-0">
        <small class="text-muted">Votre candidature ne pourra être soumise aux administrateurs qu'une fois tous ces documents uploadés.</small>
    </p>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="/student/application/create">
            @csrf
            <div class="mb-3">
                <label for="university_name" class="form-label">Université</label>
                <input type="text" class="form-control" id="university_name" name="university_name" required>
            </div>
            <div class="mb-3">
                <label for="country" class="form-label">Pays</label>
                <input type="text" class="form-control" id="country" name="country" required>
            </div>
            <div class="mb-3">
                <label for="program" class="form-label">Programme</label>
                <input type="text" class="form-control" id="program" name="program" required>
            </div>
            <div class="mb-3">
                <label for="priority_level" class="form-label">Priorité</label>
                <select class="form-select" id="priority_level" name="priority_level">
                    <option value="low">Basse</option>
                    <option value="medium" selected>Moyenne</option>
                    <option value="high">Haute</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="estimated_completion_date" class="form-label">Date estimée de fin</label>
                <input type="date" class="form-control" id="estimated_completion_date" name="estimated_completion_date">
            </div>
            <button type="submit" class="btn btn-success">Créer la candidature</button>
        </form>
    </div>
</div>
@endsection 
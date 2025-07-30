@extends('layouts.app')
@section('title', 'Ma Candidature')
@section('content')
<h2>Ma candidature</h2>
@if($application)
    <div class="card mb-3">
        <div class="card-header">Informations générales</div>
        <div class="card-body">
            <p><strong>Université :</strong> {{ $application->university_name }}</p>
            <p><strong>Pays :</strong> {{ $application->country }}</p>
            <p><strong>Programme :</strong> {{ $application->program }}</p>
            <p><strong>Statut :</strong> {{ $application->status }}</p>
            <p><strong>Priorité :</strong> {{ $application->priority_level }}</p>
            <p><strong>Date estimée de fin :</strong> {{ $application->estimated_completion_date }}</p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">Étapes du dossier</div>
        <div class="card-body">
            <ul>
                @foreach($application->steps as $step)
                    <li>{{ $step->step_name }} - <span class="badge bg-{{ $step->status == 'completed' ? 'success' : ($step->status == 'in_progress' ? 'warning' : 'secondary') }}">{{ $step->status }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">Documents</div>
        <div class="card-body">
            <ul>
                @foreach($application->documents as $doc)
                    <li>{{ $doc->document_type }} - <span class="badge bg-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'pending' ? 'warning' : 'danger') }}">{{ $doc->status }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
@else
    <p>Aucune candidature trouvée.</p>
@endif
@endsection 
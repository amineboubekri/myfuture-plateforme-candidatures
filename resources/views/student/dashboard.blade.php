@extends('layouts.app')
@section('title', 'Dashboard Étudiant')
@section('content')
<h2>Tableau de bord étudiant</h2>
@if(!isset($application_status) || !$application_status)
    <div class="alert alert-info">Vous n'avez pas encore de candidature. <a href="/student/application/create" class="btn btn-sm btn-success ms-2">Créer une candidature</a></div>
@endif
<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Progression de la candidature</div>
            <div class="card-body">
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress ?? 0 }}%">{{ $progress ?? 0 }}%</div>
                </div>
                <p><strong>Statut :</strong> {{ $application_status ?? 'Aucune candidature' }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Documents manquants</div>
            <div class="card-body">
                @if(isset($missing_documents) && count($missing_documents))
                    <ul>
                        @foreach($missing_documents as $doc)
                            <li>{{ $doc->document_type }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>Aucun document manquant.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Notifications récentes</div>
            <div class="card-body">
                @if(isset($notifications) && count($notifications))
                    <ul>
                        @foreach($notifications as $notif)
                            <li><strong>{{ $notif->title }}</strong> : {{ $notif->message }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>Aucune notification.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 
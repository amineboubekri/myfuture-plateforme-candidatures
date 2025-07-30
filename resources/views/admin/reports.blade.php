@extends('layouts.app')
@section('title', 'Reporting')
@section('content')
<h2>Reporting - Candidatures</h2>
<div class="card mb-3">
    <div class="card-header">Filtres</div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="country" class="form-control" placeholder="Pays" value="{{ request('country') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous statuts</option>
                        <option value="pending">En attente</option>
                        <option value="in_progress">En cours</option>
                        <option value="completed">Terminée</option>
                        <option value="rejected">Rejetée</option>
                        <option value="approved">Approuvée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </div>
        </form>
        <div class="mt-3">
            <a href="?export=excel{{ request()->getQueryString() ? '&'.request()->getQueryString() : '' }}" class="btn btn-success btn-sm">Exporter Excel</a>
            <a href="?export=pdf{{ request()->getQueryString() ? '&'.request()->getQueryString() : '' }}" class="btn btn-danger btn-sm">Exporter PDF</a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">Rapport des candidatures</div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Université</th>
                    <th>Pays</th>
                    <th>Programme</th>
                    <th>Statut</th>
                    <th>Priorité</th>
                    <th>Date création</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td>{{ $app->user->name }}</td>
                        <td>{{ $app->university_name }}</td>
                        <td>{{ $app->country }}</td>
                        <td>{{ $app->program }}</td>
                        <td>{{ $app->status }}</td>
                        <td>{{ $app->priority_level }}</td>
                        <td>{{ $app->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $applications->links() }}
    </div>
</div>
@endsection 
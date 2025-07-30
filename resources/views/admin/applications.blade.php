@extends('layouts.app')
@section('title', 'Gestion des candidatures')
@section('content')
<h2>Gestion des candidatures</h2>
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
    </div>
</div>
<div class="card">
    <div class="card-header">Liste des candidatures</div>
    <div class="card-body">
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Université</th>
                    <th>Pays</th>
                    <th>Programme</th>
                    <th>Statut</th>
                    <th>Priorité</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td>{{ $app->user->name }}</td>
                        <td>{{ $app->university_name }}</td>
                        <td>{{ $app->country }}</td>
                        <td>{{ $app->program }}</td>
                        <td><span class="badge bg-{{ $app->status == 'completed' ? 'success' : ($app->status == 'pending' ? 'warning' : 'secondary') }}">{{ $app->status }}</span></td>
                        <td><span class="badge bg-{{ $app->priority_level == 'high' ? 'danger' : ($app->priority_level == 'medium' ? 'warning' : 'secondary') }}">{{ $app->priority_level }}</span></td>
                        <td>{{ $app->created_at->format('d/m/Y') }}</td>
                        <td>
                            <form method="POST" action="/admin/applications/{{ $app->id }}/status" class="d-inline">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm d-inline w-auto" style="color:#000 !important;background:#fff !important;border:1px solid #ddd;margin-right:5px;">
                                    <option value="pending" {{ $app->status == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="in_progress" {{ $app->status == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                    <option value="completed" {{ $app->status == 'completed' ? 'selected' : '' }}>Terminée</option>
                                    <option value="rejected" {{ $app->status == 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                    <option value="approved" {{ $app->status == 'approved' ? 'selected' : '' }}>Approuvée</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-success" style="color:#fff !important;">OK</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $applications->links() }}
    </div>
</div>
@endsection 
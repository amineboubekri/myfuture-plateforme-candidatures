@extends('layouts.app')
@section('title', 'Validation des documents')
@section('content')
<h2>Validation des documents</h2>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header">Filtres</div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="document_type" class="form-control" placeholder="Type de document" value="{{ request('document_type') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Documents à valider</div>
    <div class="card-body">
        @if(count($documents))
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Date upload</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        <tr>
                            <td>{{ $doc->application->user->name ?? '-' }}</td>
                            <td>{{ $doc->document_type }}</td>
                            <td><span class="badge bg-{{ $doc->status == 'approved' ? 'success' : ($doc->status == 'pending' ? 'warning' : 'danger') }}">{{ $doc->status }}</span></td>
                            <td>{{ $doc->uploaded_at }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/admin/documents/{{ $doc->id }}/view" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="/admin/documents/{{ $doc->id }}/download" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                </div>
                                <form method="POST" action="/admin/documents/{{ $doc->id }}/validate" class="d-inline mt-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="input-group input-group-sm">
                                        <select name="status" class="form-select">
                                            <option value="approved">Approuver</option>
                                            <option value="rejected">Rejeter</option>
                                        </select>
                                        <input type="text" name="comments" class="form-control" placeholder="Commentaire">
                                        <button type="submit" class="btn btn-success">Valider</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $documents->links() }}
        @else
            <p>Aucun document trouvé.</p>
        @endif
    </div>
</div>
@endsection 
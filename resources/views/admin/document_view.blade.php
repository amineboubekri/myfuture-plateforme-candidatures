@extends('layouts.app')
@section('title', 'Consulter le document')
@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-6 mb-2">
                <i class="fas fa-file-alt me-3"></i>Consultation de Document
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-user me-2"></i>{{ $document->application->user->name }} • 
                <i class="fas fa-calendar me-2"></i>{{ date('d M Y', strtotime($document->uploaded_at)) }}
            </p>
        </div>
        <a href="/admin/documents" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>

    <div class="row">
        <!-- Document Information -->
        <div class="col-md-4">
            <!-- Document Details Card -->
            <div class="card mb-4 document-info-card">
                <div class="card-header">
                    <h5><i class="fas fa-file-alt me-2"></i>Informations du Document</h5>
                </div>
                <div class="card-body p-0">
                    <!-- Document Type -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="info-content">
                            <label>Type de document</label>
                            <span class="document-type">{{ $document->document_type }}</span>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-signal"></i>
                        </div>
                        <div class="info-content">
                            <label>Statut</label>
                            <span class="status-badge status-{{ $document->status }}">
                                @if($document->status == 'approved')
                                    <i class="fas fa-check-circle me-1"></i>Approuvé
                                @elseif($document->status == 'rejected')
                                    <i class="fas fa-times-circle me-1"></i>Rejeté
                                @else
                                    <i class="fas fa-clock me-1"></i>En attente
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <!-- Upload Date -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <label>Date d'upload</label>
                            <span>{{ date('d M Y à H:i', strtotime($document->uploaded_at)) }}</span>
                        </div>
                    </div>
                    
                    @if($document->comments)
                    <!-- Comments -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="info-content">
                            <label>Commentaires</label>
                            <span class="comment-text">{{ $document->comments }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Student Information Card -->
            <div class="card mb-4 student-info-card">
                <div class="card-header">
                    <h5><i class="fas fa-user-graduate me-2"></i>Informations Étudiant</h5>
                </div>
                <div class="card-body p-0">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-content">
                            <label>Nom complet</label>
                            <span>{{ $document->application->user->name }}</span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <label>Email</label>
                            <span class="email-text">{{ $document->application->user->email }}</span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="info-content">
                            <label>Université</label>
                            <span>{{ $document->application->university_name }}</span>
                        </div>
                    </div>
                    
                    <div class="info-item border-0">
                        <div class="info-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="info-content">
                            <label>Programme</label>
                            <span>{{ $document->application->program }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card actions-card">
                <div class="card-header">
                    <h5><i class="fas fa-tools me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/admin/documents/{{ $document->id }}/download" class="btn btn-info w-100 mb-3">
                        <i class="fas fa-download me-2"></i>Télécharger le fichier
                    </a>
                    <a href="{{ url('/admin/documents/' . $document->id . '/serve') }}" target="_blank" class="btn btn-secondary w-100">
                        <i class="fas fa-external-link-alt me-2"></i>Ouvrir dans un nouvel onglet
                    </a>
                </div>
            </div>

            <!-- Validation Form -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-check-circle"></i> Validation du document</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/documents/{{ $document->id }}/validate">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Décision</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Sélectionnez une décision</option>
                                <option value="approved" {{ $document->status == 'approved' ? 'selected' : '' }}>
                                    <i class="fas fa-check"></i> Approuver
                                </option>
                                <option value="rejected" {{ $document->status == 'rejected' ? 'selected' : '' }}>
                                    <i class="fas fa-times"></i> Rejeter
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comments" class="form-label">Commentaires (optionnel)</label>
                            <textarea name="comments" id="comments" class="form-control" rows="4" 
                                placeholder="Ajoutez vos commentaires ou raisons de rejet...">{{ $document->comments }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer la décision
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Document Preview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-eye"></i> Aperçu du document</h5>
                </div>
                <div class="card-body">
                    @php
                        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                        $fileUrl = url('/admin/documents/' . $document->id . '/serve');
                    @endphp

                    @if(in_array($fileExtension, ['pdf']))
                        <!-- PDF Viewer -->
                        <div class="text-center mb-3">
                            <p class="text-muted">Document PDF - Cliquez sur le bouton ci-dessous pour ouvrir dans un nouvel onglet</p>
                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Ouvrir le PDF dans un nouvel onglet
                            </a>
                        </div>
                        <iframe src="{{ $fileUrl }}" width="100%" height="600px" style="border: 1px solid #ddd; border-radius: 5px;">
                            <p>Votre navigateur ne supporte pas l'affichage PDF. 
                               <a href="{{ $fileUrl }}" target="_blank">Cliquez ici pour télécharger le fichier.</a>
                            </p>
                        </iframe>
                    @elseif(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                        <!-- Image Viewer -->
                        <div class="text-center">
                            <img src="{{ $fileUrl }}" alt="Document Image" class="img-fluid" 
                                 style="max-height: 600px; border: 1px solid #ddd; border-radius: 5px; cursor: zoom-in;"
                                 onclick="window.open('{{ $fileUrl }}', '_blank')">
                            <p class="text-muted mt-2">Cliquez sur l'image pour l'agrandir</p>
                        </div>
                    @else
                        <!-- Unsupported File Type -->
                        <div class="text-center py-5">
                            <i class="fas fa-file fa-3x text-muted mb-3"></i>
                            <h5>Aperçu non disponible</h5>
                            <p class="text-muted">
                                Ce type de fichier (.{{ $fileExtension }}) ne peut pas être prévisualisé dans le navigateur.<br>
                                Utilisez le bouton de téléchargement pour consulter le document.
                            </p>
                            <a href="/admin/documents/{{ $document->id }}/download" class="btn btn-primary">
                                <i class="fas fa-download"></i> Télécharger le fichier
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Document View Specific Styles */
.info-item {
    display: flex;
    align-items: flex-start;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--glass-border);
    transition: all 0.3s ease;
}

.info-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.info-item.border-0 {
    border-bottom: none;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.info-icon i {
    font-size: 1.1rem;
    color: white;
}

.info-content {
    flex: 1;
    min-width: 0;
}

.info-content label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted) !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.info-content span {
    display: block;
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--text-primary) !important;
    word-wrap: break-word;
}

.document-type {
    background: var(--accent-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700 !important;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-approved {
    background: rgba(17, 153, 142, 0.2);
    color: #38ef7d !important;
    border: 1px solid rgba(56, 239, 125, 0.3);
}

.status-rejected {
    background: rgba(245, 87, 108, 0.2);
    color: #f5576c !important;
    border: 1px solid rgba(245, 87, 108, 0.3);
}

.status-pending {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107 !important;
    border: 1px solid rgba(255, 193, 7, 0.3);
    animation: pulse-status 2s infinite;
}

@keyframes pulse-status {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
}

.email-text {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.85rem !important;
    background: rgba(79, 172, 254, 0.1);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid rgba(79, 172, 254, 0.2);
}

.comment-text {
    font-style: italic;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.75rem;
    border-radius: 8px;
    border-left: 3px solid var(--accent-gradient);
    margin-top: 0.5rem;
}

/* Enhanced PDF Viewer */
iframe {
    border: none !important;
    border-radius: 15px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    background: white;
}

/* Card Variants */
.document-info-card::before {
    background: var(--accent-gradient);
}

.student-info-card::before {
    background: var(--primary-gradient);
}

.actions-card::before {
    background: var(--secondary-gradient);
}

/* Responsive Design */
@media (max-width: 768px) {
    .info-item {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
    }
    
    .info-icon {
        margin-right: 0;
        margin-bottom: 0.75rem;
    }
    
    .display-6 {
        font-size: 1.5rem;
    }
}

/* Animation for cards */
.card {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.document-info-card {
    animation-delay: 0.1s;
}

.student-info-card {
    animation-delay: 0.2s;
}

.actions-card {
    animation-delay: 0.3s;
}
</style>
@endsection

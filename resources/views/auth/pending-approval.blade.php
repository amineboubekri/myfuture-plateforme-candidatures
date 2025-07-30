@extends('layouts.app')

@section('title', 'Compte en attente d\'approbation')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="fw-light my-2">
                        <i class="fas fa-clock me-2"></i>
                        Compte en attente d'approbation
                    </h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-hourglass-half fa-4x text-warning mb-3"></i>
                        <h4 class="mb-3">Votre inscription a été soumise avec succès !</h4>
                        <p class="text-muted mb-4">
                            Votre compte est actuellement en cours d'examen par nos administrateurs. 
                            Vous recevrez une notification par email dès que votre compte sera approuvé 
                            et que vous pourrez accéder à la plateforme.
                        </p>
                    </div>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Que se passe-t-il maintenant ?</strong>
                        <ul class="list-unstyled mt-2 mb-0">
                            <li>✓ Votre demande d'inscription a été reçue</li>
                            <li>⏳ Un administrateur examine votre profil</li>
                            <li>📧 Vous recevrez un email de confirmation</li>
                            <li>🚀 Vous pourrez ensuite accéder à votre espace</li>
                        </ul>
                    </div>

                    <p class="text-muted small mb-4">
                        Ce processus prend généralement 24 à 48 heures pendant les jours ouvrables.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Retour à la connexion
                        </a>
                        <a href="/" class="btn btn-link">
                            <i class="fas fa-home me-2"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        Des questions ? Contactez-nous à 
                        <a href="mailto:support@myfuture.com" class="text-primary">support@myfuture.com</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

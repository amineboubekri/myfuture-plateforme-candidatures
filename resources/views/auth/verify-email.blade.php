@extends('layouts.app')

@section('title', 'Vérifiez votre email')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-4" style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.15);">
                    <div class="card-body p-5">
                        <!-- Logo or Title -->
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-envelope-open-text text-white" style="font-size: 3rem;"></i>
                            </div>
                            <h2 class="text-white fw-bold mb-2">Vérifiez votre email</h2>
                            <p class="text-white opacity-75 mb-0">Un lien de vérification a été envoyé</p>
                        </div>

                        <!-- Status Messages -->
                        @if (session('status'))
                            <div class="alert alert-success border-0 rounded-3 text-dark mb-4" style="background: rgba(255, 255, 255, 0.9);">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        <!-- Verification Message -->
                        <div class="text-center mb-4">
                            <p class="text-white mb-3">
                                Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer ?
                            </p>
                            <p class="text-white opacity-75 small">
                                Si vous n'avez pas reçu l'e-mail, nous vous en enverrons un autre avec plaisir.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mb-3">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-light btn-lg fw-semibold rounded-3 w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i>Renvoyer l'email de vérification
                                </button>
                            </form>
                        </div>

                        <!-- Logout Option -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-white text-decoration-none opacity-75 p-0">
                                    <i class="fas fa-sign-out-alt me-1"></i>Se déconnecter
                                </button>
                            </form>
                        </div>

                        <!-- Back to Login -->
                        <div class="text-center mt-4">
                            <a href="{{ route('login') }}" class="text-white text-decoration-none opacity-75">
                                <i class="fas fa-arrow-left me-2"></i>Retour à la connexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

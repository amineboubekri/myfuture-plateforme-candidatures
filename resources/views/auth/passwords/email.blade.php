@extends('layouts.app')

@section('title', 'Mot de passe oublié')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header text-center">
                    <div class="mb-3">
                        <i class="fas fa-key text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-0">Mot de passe oublié</h4>
                    <p class="text-muted mt-2 mb-0">Réinitialisez votre mot de passe</p>
                </div>
                
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <p class="text-muted">
                            Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Adresse Email
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="votre.email@exemple.com"
                                   autofocus
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
                        </button>
                        
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Additional Help -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-question-circle me-2"></i>Besoin d'aide ?
                    </h6>
                    <ul class="text-muted small mb-0">
                        <li>Vérifiez votre dossier spam/courrier indésirable</li>
                        <li>Le lien expire après 60 minutes</li>
                        <li>Contactez l'administrateur si le problème persiste</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

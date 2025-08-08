@extends('layouts.app')

@section('title', 'Changer le mot de passe')

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
                                <i class="fas fa-key text-white" style="font-size: 3rem;"></i>
                            </div>
                            <h2 class="text-white fw-bold mb-2">Changer le mot de passe</h2>
                            <p class="text-white opacity-75 mb-0">Mettez à jour votre mot de passe</p>
                        </div>

                        <!-- Success Message -->
                        @if (session('success'))
                            <div class="alert alert-success border-0 rounded-3 text-dark mb-4" style="background: rgba(255, 255, 255, 0.9);">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3 text-dark mb-4" style="background: rgba(255, 255, 255, 0.9);">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Change Password Form -->
                        <form method="POST" action="{{ route('password.change') }}">
                            @csrf
                            
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label for="current_password" class="form-label text-white fw-semibold">
                                    <i class="fas fa-lock me-2"></i>Mot de passe actuel
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg rounded-3 border-0 @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);"
                                       placeholder="Entrez votre mot de passe actuel"
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label text-white fw-semibold">
                                    <i class="fas fa-key me-2"></i>Nouveau mot de passe
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg rounded-3 border-0 @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);"
                                       placeholder="Entrez votre nouveau mot de passe"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-white opacity-75 mt-2">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Le mot de passe doit contenir au moins 8 caractères
                                    </small>
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label text-white fw-semibold">
                                    <i class="fas fa-check-double me-2"></i>Confirmer le nouveau mot de passe
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg rounded-3 border-0" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);"
                                       placeholder="Confirmez votre nouveau mot de passe"
                                       required>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-light btn-lg fw-semibold rounded-3 py-3">
                                    <i class="fas fa-save me-2"></i>Mettre à jour le mot de passe
                                </button>
                            </div>
                        </form>

                        <!-- Navigation Links -->
                        <div class="text-center">
                            <div class="row">
                                <div class="col-6">
                                    @if(auth()->user()->role === 'admin')
                                        <a href="/admin/dashboard" class="text-white text-decoration-none opacity-75">
                                            <i class="fas fa-home me-1"></i>Tableau de bord
                                        </a>
                                    @else
                                        <a href="{{ route('student.dashboard') }}" class="text-white text-decoration-none opacity-75">
                                            <i class="fas fa-home me-1"></i>Tableau de bord
                                        </a>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-white text-decoration-none opacity-75 p-0">
                                            <i class="fas fa-sign-out-alt me-1"></i>Se déconnecter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.95) !important;
    }
    
    .btn-light:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endsection

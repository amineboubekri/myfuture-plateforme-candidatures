@extends('layouts.app')
@section('title', 'Compléter le Profil')
@section('content')
<div class="container">
    <h2 class="my-4">Compléter votre Profil</h2>

    <form action="{{ route('student.profile.update') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Nom Complet</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Numéro de Téléphone</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Adresse</label>
            <textarea name="address" id="address" class="form-control" rows="3" required>{{ $user->address ?? '' }}</textarea>
        </div>

        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date de Naissance</label>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ $user->date_of_birth ?? '' }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour le Profil</button>
        
    </form>
</div>
@endsection

<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile setup form
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return view('student.profile.setup', compact('user'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'date_of_birth' => 'required|date|before:today',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.date' => 'Veuillez entrer une date valide.',
            'date_of_birth.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
        ]);
        
        $user->update($data);
        
        // Update profile completion status
        $isComplete = $user->updateProfileCompletionStatus();
        
        if ($isComplete) {
            return redirect()->route('student.dashboard')
                ->with('success', 'Votre profil a été complété avec succès ! Vous pouvez maintenant créer une candidature.');
        } else {
            return back()->with('warning', 'Profil mis à jour. Veuillez compléter tous les champs requis.');
        }
    }

    /**
     * Show the profile edit form
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        return view('student.profile.edit', compact('user'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('login');
        }
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Check if user is approved (only for students)
            if ($user->role === 'student' && !$user->is_approved) {
                Auth::logout();
                return redirect('/pending-approval')
                    ->with('message', 'Votre compte est en attente d\'approbation par un administrateur.');
            }
            
            if ($user->role === 'admin') {
                return redirect('/admin/dashboard')->with('success', 'Connexion réussie ! Bienvenue dans l\'espace administrateur.');
            } else {
                return redirect('/student/dashboard')->with('success', 'Connexion réussie ! Bienvenue sur votre espace personnel.');
            }
        }
        
        return back()->withErrors(['email' => 'Identifiants invalides. Vérifiez votre email et mot de passe.'])->withInput();
    }

    public function register(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('register');
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'is_approved' => false, // New students need approval
            'email_verified_at' => now(), // Mark email as verified since we're using approval system
        ]);
        
        return redirect('/pending-approval')
            ->with('success', 'Votre inscription a été enregistrée avec succès ! Votre compte est en attente d\'approbation.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Show the email verification page
     */
    public function verifyNotice()
    {
        return view('auth.verify-email');
    }

    /**
     * Handle email verification
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect('/student/dashboard')->with('status', 'Votre email a été vérifié avec succès!');
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Le lien de vérification a été renvoyé!');
    }

    /**
     * Show password change form
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('status', 'Votre mot de passe a été modifié avec succès!');
    }
    
    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}

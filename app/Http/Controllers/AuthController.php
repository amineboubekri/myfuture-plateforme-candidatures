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
            
            // Check if email is verified
            if ($user->email_verified_at === null) {
                return redirect('/email/verify')
                    ->with('message', 'Veuillez vérifier votre adresse email avant de continuer.');
            }
            
            // Check if user is approved (only for students)
            if ($user->role === 'student' && !$user->is_approved) {
                Auth::logout();
                return redirect('/pending-approval')
                    ->with('message', 'Votre compte est en attente d\'approbation par un administrateur.');
            }
            
            // Check if 2FA is enabled for this user (only for students)
            if ($user->role === 'student' && $user->google2fa_enabled) {
                // Don't mark as 2FA verified yet - redirect to verification
                return redirect()->route('2fa.verify');
            }
            
            // Mark as 2FA verified (normal login flow or admin)
            $request->session()->put('2fa_verified', true);
            
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
            'is_approved' => false
        ]);
        
        // Log in the user so they can access verification page
        Auth::login($user);
        
        // Send verification email
        event(new Registered($user));

        return redirect('/email/verify')
            ->with('success', 'Votre inscription a été enregistrée avec succès ! Un email de vérification a été envoyé. Veuillez vérifier votre email.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget('2fa_verified');
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
        $user = $request->user();
        
        // After email verification, redirect based on approval status
        if ($user->role === 'student' && !$user->is_approved) {
            return redirect('/pending-approval')
                ->with('status', 'Votre email a été vérifié avec succès ! Votre compte est maintenant en attente d\'approbation par un administrateur.');
        }
        
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

        return back()->with('success', 'Votre mot de passe a été modifié avec succès!');
    }
    
    /**
     * Show pending approval page
     */
    public function pendingApproval()
    {
        return view('auth.pending-approval');
    }
}

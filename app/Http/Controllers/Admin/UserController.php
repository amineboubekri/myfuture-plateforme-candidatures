<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filter by approval status
        if ($request->has('approval_status') && $request->approval_status !== '') {
            if ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            }
        }
        
        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }
        
        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'pending_approvals' => User::where('is_approved', false)->where('role', 'student')->count(),
            'approved_users' => User::where('is_approved', true)->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:student,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle doit être étudiant ou administrateur.',
        ]);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'is_approved' => $request->role === 'admin' ? true : ($request->has('is_approved') ? true : false),
        ]);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }
    
    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:student,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle doit être étudiant ou administrateur.',
        ]);
        
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ];
        
        // Handle password update if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:8|confirmed',
            ], [
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ]);
            
            $updateData['password'] = Hash::make($request->password);
        }
        
        // Handle approval status
        if ($request->role === 'admin') {
            $updateData['is_approved'] = true;
        } else {
            $updateData['is_approved'] = $request->has('is_approved');
        }
        
        $user->update($updateData);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }
    
    /**
     * Approve a user
     */
    public function approve(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Les administrateurs sont automatiquement approuvés.');
        }
        
        $user->update(['is_approved' => true]);
        
        return back()->with('success', 'Utilisateur approuvé avec succès.');
    }
    
    /**
     * Revoke user approval
     */
    public function revoke(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Impossible de révoquer l\'approbation d\'un administrateur.');
        }
        
        $user->update(['is_approved' => false]);
        
        return back()->with('success', 'Approbation révoquée avec succès.');
    }
    
    /**
     * Toggle user approval status
     */
    public function toggleApproval(User $user)
    {
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Les administrateurs sont automatiquement approuvés.'
            ]);
        }
        
        $user->update(['is_approved' => !$user->is_approved]);
        
        return response()->json([
            'success' => true,
            'is_approved' => $user->is_approved,
            'message' => $user->is_approved ? 'Utilisateur approuvé.' : 'Approbation révoquée.'
        ]);
    }
    
    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|min:8',
        ], [
            'new_password.required' => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', "Mot de passe de {$user->name} réinitialisé avec succès.");
    }

    /**
     * Delete a user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();
        
        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }
}

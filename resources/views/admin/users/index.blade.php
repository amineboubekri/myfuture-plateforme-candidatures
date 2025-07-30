@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users me-2"></i>
                    Gestion des Utilisateurs
                </h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                        <i class="fas fa-filter me-1"></i>
                        Filtres
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['total_users'] }}</h4>
                                    <p class="card-text">Total Utilisateurs</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['pending_approvals'] }}</h4>
                                    <p class="card-text">En Attente</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['approved_users'] }}</h4>
                                    <p class="card-text">Approuvés</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['total_students'] }}</h4>
                                    <p class="card-text">Étudiants</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="collapse mb-4" id="filtersCollapse">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.users.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Rechercher</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Nom ou email...">
                                </div>
                                <div class="col-md-3">
                                    <label for="role" class="form-label">Rôle</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="">Tous les rôles</option>
                                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Étudiant</option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="approval_status" class="form-label">Statut d'approbation</label>
                                    <select class="form-select" id="approval_status" name="approval_status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>
                                        Filtrer
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Liste des Utilisateurs
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-shield-alt me-1"></i>
                                                Administrateur
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-graduation-cap me-1"></i>
                                                Étudiant
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Auto-approuvé
                                            </span>
                                        @elseif($user->is_approved)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Approuvé
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>
                                                En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            {{ $user->created_at->format('d/m/Y à H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($user->role === 'student')
                                                @if($user->is_approved)
                                                    <form method="POST" action="{{ route('admin.users.revoke', $user) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                onclick="return confirm('Êtes-vous sûr de vouloir révoquer l\'approbation de cet utilisateur ?')">
                                                            <i class="fas fa-times-circle me-1"></i>
                                                            Révoquer
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Approuver
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                            
                                            <!-- Password Reset Button -->
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="showPasswordResetModal('{{ $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-key me-1"></i>
                                                Reset MDP
                                            </button>
                                            
                                            @if($user->role === 'admin' && $user->id !== auth()->id())
                                                <span class="text-muted small ms-2">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Administrateur
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>Aucun utilisateur trouvé.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Password Reset Modal -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordResetModalLabel">
                    <i class="fas fa-key me-2"></i>
                    Réinitialiser le mot de passe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="passwordResetForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Utilisateur:</label>
                        <p id="userNameDisplay" class="text-muted mb-3"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="password" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required minlength="8">
                        <div id="passwordMatchError" class="text-danger small" style="display: none;">Les mots de passe ne correspondent pas.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="resetPasswordBtn">
                        <i class="fas fa-key me-1"></i>
                        Réinitialiser le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.table-responsive {
    border-radius: 0.5rem;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.badge {
    font-size: 0.8em;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-color: rgba(0,0,0,0.1);
}

.table-hover tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
        margin-right: 0;
    }
}
</style>

<script>
// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('#role, #approval_status');
    
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});

// Password Reset Modal Functions
function showPasswordResetModal(userId, userName) {
    // Set the form action URL
    const form = document.getElementById('passwordResetForm');
    form.action = `/admin/users/${userId}/reset-password`;
    
    // Set the user name in the modal
    document.getElementById('userNameDisplay').textContent = userName;
    
    // Clear form fields
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordMatchError').style.display = 'none';
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
    modal.show();
}

// Password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('newPassword');
    
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
});

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const errorDiv = document.getElementById('passwordMatchError');
    const submitBtn = document.getElementById('resetPasswordBtn');
    
    function validatePasswords() {
        if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
            errorDiv.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            errorDiv.style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});

// Form submission with loading state
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordResetForm');
    const submitBtn = document.getElementById('resetPasswordBtn');
    
    form.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            document.getElementById('passwordMatchError').style.display = 'block';
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Réinitialisation...';
    });
});
</script>
@endsection

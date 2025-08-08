@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<!-- Dashboard Header -->
<div class="dashboard-header mb-5">
    <h1 class="display-4 fw-bold text-gradient-primary mb-2">Tableau de Bord Administrateur</h1>
    <p class="lead text-light opacity-75">Vue d'ensemble des candidatures et métriques de performance</p>
</div>

<!-- Stats Overview Cards -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card glass-card hover-lift">
            <div class="stat-icon bg-gradient-primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number text-gradient-primary">{{ $total_applications }}</h3>
                <p class="stat-label text-light opacity-75">Total Candidatures</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card glass-card hover-lift">
            <div class="stat-icon bg-gradient-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number text-gradient-warning">{{ $pending_applications }}</h3>
                <p class="stat-label text-light opacity-75">Candidatures en Attente</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card glass-card hover-lift">
            <div class="stat-icon bg-gradient-info">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number text-gradient-info">{{ $total_users }}</h3>
                <p class="stat-label text-light opacity-75">Total Utilisateurs</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.users.index') }}?approval_status=pending" class="text-decoration-none">
            <div class="stat-card glass-card hover-lift {{ $pending_approvals > 0 ? 'border border-warning glow-warning' : '' }}">
                <div class="stat-icon bg-gradient-warning">
                    <i class="fas fa-user-clock"></i>
                    @if($pending_approvals > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $pending_approvals }}
                        </span>
                    @endif
                </div>
                <div class="stat-content">
                    <h3 class="stat-number text-gradient-warning">{{ $pending_approvals }}</h3>
                    <p class="stat-label text-light opacity-75">Approbations en Attente</p>
                    @if($pending_approvals > 0)
                        <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Action requise</small>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-4">
        <!-- Urgent Alerts Card -->
        <div class="glass-card hover-lift mb-4">
            <div class="card-header-glass">
                <h5 class="card-title text-gradient-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Alertes Urgentes
                </h5>
            </div>
            <div class="card-body-glass">
                @if(count($urgent_applications))
                    <div class="urgent-list">
                        @foreach($urgent_applications as $app)
                            <div class="urgent-item d-flex align-items-center p-3 mb-2 rounded bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                <div class="urgent-indicator bg-danger rounded-circle me-3"></div>
                                <div class="urgent-content flex-grow-1">
                                    <div class="fw-semibold text-light">{{ $app->university_name }}</div>
                                    <div class="small text-light opacity-75">{{ $app->country }} • {{ $app->status }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state text-center py-4">
                        <i class="fas fa-shield-alt text-success opacity-50 mb-3" style="font-size: 3rem;"></i>
                        <p class="text-light opacity-75 mb-0">Aucune alerte urgente</p>
                        <p class="small text-success opacity-75">Tous les dossiers sont à jour</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">
        <!-- Chart Card -->
        <div class="glass-card hover-lift mb-4">
            <div class="card-header-glass">
                <h5 class="card-title text-gradient-primary mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Candidatures par Statut
                </h5>
            </div>
            <div class="card-body-glass">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Country Distribution Card -->
        <div class="glass-card hover-lift">
            <div class="card-header-glass">
                <h5 class="card-title text-gradient-info mb-0">
                    <i class="fas fa-globe-americas me-2"></i>
                    Répartition par Pays
                </h5>
            </div>
            <div class="card-body-glass">
                <div class="country-stats">
                    @foreach($applications_by_country as $row)
                        <div class="country-item d-flex align-items-center justify-content-between p-3 mb-2 rounded bg-light bg-opacity-5 hover-bg-light">
                            <div class="country-info d-flex align-items-center">
                                <div class="country-flag bg-gradient-info rounded-circle me-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-flag text-white"></i>
                                </div>
                                <span class="country-name text-dark fw-medium">{{ $row->country }}</span>
                            </div>
                            <div class="country-count">
                                <span class="badge bg-gradient-primary text-dark rounded-pill px-3 py-2">{{ $row->total }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chart_data['labels']) !!},
            datasets: [{
                label: 'Candidatures',
                data: {!! json_encode($chart_data['data']) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.5)'
            }]
        }
    });
</script>
@endsection 
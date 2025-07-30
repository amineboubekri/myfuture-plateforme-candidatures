<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des Candidatures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8B5CF6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #8B5CF6;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #8B5CF6;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #8B5CF6;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }
        .table td {
            font-size: 10px;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-approved {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-rejected {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des Candidatures</h1>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        @if(request('status') || request('country') || request('date_from') || request('date_to'))
            <p><strong>Filtres appliqués:</strong>
                @if(request('status')) Statut: {{ ucfirst(request('status')) }} @endif
                @if(request('country')) | Pays: {{ request('country') }} @endif
                @if(request('date_from')) | Du: {{ request('date_from') }} @endif
                @if(request('date_to')) | Au: {{ request('date_to') }} @endif
            </p>
        @endif
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $applications->count() }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $applications->where('status', 'pending')->count() }}</div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $applications->where('status', 'approved')->count() }}</div>
            <div class="stat-label">Approuvées</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $applications->where('status', 'rejected')->count() }}</div>
            <div class="stat-label">Rejetées</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Pays</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Âge</th>
                <th>Spécialisation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $application)
                <tr>
                    <td>{{ $application->id }}</td>
                    <td>{{ $application->user->name ?? 'N/A' }}</td>
                    <td>{{ $application->user->email ?? 'N/A' }}</td>
                    <td>{{ $application->country }}</td>
                    <td>
                        <span class="status status-{{ $application->status }}">
                            @switch($application->status)
                                @case('pending')
                                    En attente
                                    @break
                                @case('approved')
                                    Approuvée
                                    @break
                                @case('rejected')
                                    Rejetée
                                    @break
                                @default
                                    {{ $application->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>{{ $application->created_at->format('d/m/Y') }}</td>
                    <td>{{ $application->age ?? 'N/A' }}</td>
                    <td>{{ $application->specialization ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Ce rapport contient {{ $applications->count() }} candidature(s) au total.</p>
        <p>MyFuture - Plateforme de Candidatures</p>
    </div>
</body>
</html>

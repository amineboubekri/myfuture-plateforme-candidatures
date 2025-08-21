<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class SimpleSecurityAuditTest extends TestCase
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
    public function sql_injection_protection()
    {
        // Test d'injection SQL basique
        $sqlPayloads = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "admin'--"
        ];

        foreach ($sqlPayloads as $payload) {
            $response = $this->get("/admin/applications?search={$payload}");
            
            // Vérifier qu'il n'y a pas d'erreur SQL
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "SQL Injection possible avec: {$payload}");
        }
    }

    // ========================================
    // AUDIT XSS
    // ========================================

    /** @test */
    public function xss_protection()
    {
        $user = User::factory()->create([
            'email' => 'xss@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'xss@test.com',
            'password' => 'Password123!'
        ]);

        // Test XSS basique
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(\'XSS\')">',
            'javascript:alert("XSS")'
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->post('/student/messages/send', [
                'application_id' => 1,
                'content' => $payload
            ]);

            // Vérifier que le payload n'est pas exécuté
            $this->assertNotEquals(500, $response->getStatusCode(), 
                "XSS possible avec: {$payload}");
        }
    }

    // ========================================
    // AUDIT CSRF
    // ========================================

    /** @test */
    public function csrf_protection()
    {
        $user = User::factory()->create([
            'email' => 'csrf@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'csrf@test.com',
            'password' => 'Password123!'
        ]);

        // Test sans token CSRF
        $response = $this->post('/student/messages/send', [
            'application_id' => 1,
            'content' => 'Test message'
        ], [
            'X-CSRF-TOKEN' => ''
        ]);

        // Vérifier que la requête est rejetée
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Protection CSRF manquante");
    }

    // ========================================
    // AUDIT D'UPLOAD DE FICHIERS
    // ========================================

    /** @test */
    public function file_upload_security()
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
            'test.php' => '<?php phpinfo(); ?>'
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

    // ========================================
    // AUDIT D'ACCÈS NON AUTORISÉ
    // ========================================

    /** @test */
    public function unauthorized_access_protection()
    {
        // Test d'accès sans authentification
        $protectedRoutes = [
            '/student/dashboard',
            '/student/application',
            '/student/documents',
            '/admin/dashboard',
            '/admin/users'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que l'accès est bloqué
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Accès non autorisé possible sur: {$route}");
        }
    }

    /** @test */
    public function privilege_escalation_protection()
    {
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
            '/admin/applications'
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que l'accès est refusé
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Élévation de privilèges possible sur: {$route}");
        }
    }

    // ========================================
    // AUDIT IDOR
    // ========================================

    /** @test */
    public function idor_protection()
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

        // Création d'applications
        $app1 = Application::factory()->create(['user_id' => $user1->id]);
        $app2 = Application::factory()->create(['user_id' => $user2->id]);

        // Connexion avec user1
        $this->post('/login', [
            'email' => 'user1@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative d'accès à l'application de user2
        $response = $this->get("/student/application/{$app2->id}");
        
        // Vérifier que l'accès est refusé
        $this->assertNotEquals(200, $response->getStatusCode(), 
            "Vulnérabilité IDOR détectée");
    }

    // ========================================
    // AUDIT DE FUITE D'INFORMATIONS
    // ========================================

    /** @test */
    public function information_disclosure_protection()
    {
        // Test de fuite d'informations sensibles
        $sensitiveRoutes = [
            '/.env',
            '/config/database.php',
            '/storage/logs/laravel.log',
            '/composer.json'
        ];

        foreach ($sensitiveRoutes as $route) {
            $response = $this->get($route);
            
            // Vérifier que les fichiers sensibles ne sont pas accessibles
            $this->assertNotEquals(200, $response->getStatusCode(), 
                "Fuite d'informations possible sur: {$route}");
        }
    }

    /** @test */
    public function error_information_disclosure_protection()
    {
        // Test de fuite d'informations dans les erreurs
        $errorPayloads = [
            "' OR 1=1 --",
            "<script>alert('xss')</script>",
            "../../../etc/passwd"
        ];

        foreach ($errorPayloads as $payload) {
            $response = $this->get("/admin/applications?search={$payload}");
            
            // Vérifier que les erreurs ne révèlent pas d'informations sensibles
            if ($response->getStatusCode() === 500) {
                $content = $response->getContent();
                
                // Vérifier qu'il n'y a pas de fuite d'informations sensibles
                $this->assertStringNotContainsString('SQLSTATE', $content, 
                    "Fuite d'informations SQL avec: {$payload}");
                $this->assertStringNotContainsString('database', $content, 
                    "Fuite d'informations de base de données avec: {$payload}");
            }
        }
    }

    // ========================================
    // AUDIT DE CONFIGURATION
    // ========================================

    /** @test */
    public function security_headers_check()
    {
        $response = $this->get('/');
        
        $headers = $response->headers;
        
        // Vérifier la présence des en-têtes de sécurité
        $securityHeaders = [
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection'
        ];

        foreach ($securityHeaders as $header) {
            $this->assertTrue($headers->has($header) || $headers->has(strtolower($header)), 
                "En-tête de sécurité manquant: {$header}");
        }
    }

    /** @test */
    public function session_security_check()
    {
        // Vérifier la configuration de session
        $this->assertTrue(Config::get('session.secure'), 'Session non sécurisée');
        $this->assertTrue(Config::get('session.http_only'), 'Session non http_only');
    }

    /** @test */
    public function password_policy_check()
    {
        // Test de politique de mot de passe faible
        $weakPasswords = [
            '123456',
            'password',
            'admin',
            'qwerty'
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
    public function rate_limiting_check()
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
            "Rate limiting manquant");
    }

    // ========================================
    // AUDIT DE VALIDATION
    // ========================================

    /** @test */
    public function input_validation_check()
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
            'date' => 'invalid-date'
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
    // AUDIT DE HACHAGE
    // ========================================

    /** @test */
    public function password_hashing_check()
    {
        $user = User::factory()->create([
            'email' => 'hash@test.com',
            'password' => 'Password123!',
            'role' => 'student'
        ]);

        // Vérifier que le mot de passe est hashé
        $this->assertNotEquals('Password123!', $user->password, 
            "Mot de passe non hashé");
        
        // Vérifier que le hash fonctionne
        $this->assertTrue(Hash::check('Password123!', $user->password), 
            "Hash de mot de passe incorrect");
    }

    // ========================================
    // AUDIT DE CONFIGURATION ENVIRONNEMENT
    // ========================================

    /** @test */
    public function environment_configuration_check()
    {
        // Vérifier la configuration de l'environnement
        $this->assertNotEquals('production', app()->environment(), 
            "Application en mode production pendant les tests");
        
        $this->assertNotEmpty(Config::get('app.key'), 
            "Clé d'application manquante");
    }

    // ========================================
    // AUDIT DE LOGS
    // ========================================

    /** @test */
    public function security_logging_check()
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

        // Vérifier que les logs existent
        $logFile = storage_path('logs/laravel.log');
        $this->assertFileExists($logFile, "Fichier de log manquant");
    }

    // ========================================
    // AUDIT DE SESSION
    // ========================================

    /** @test */
    public function session_timeout_check()
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
    // AUDIT DE LOGIQUE MÉTIER
    // ========================================

    /** @test */
    public function business_logic_validation()
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
}

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

class AdaptedUserAcceptanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================
    // TESTS D'ACCEPTATION ÉTUDIANT
    // ========================================

    /** @test */
    public function student_can_register_and_login()
    {
        // 1. Inscription
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $response->assertStatus(302); // Redirection après inscription

        // 2. Connexion
        $loginResponse = $this->post('/login', [
            'email' => 'student@test.com',
            'password' => 'Password123!'
        ]);

        $loginResponse->assertStatus(302); // Redirection après connexion

        // 3. Vérification accès dashboard (redirection vers profile setup)
        $dashboardResponse = $this->get('/student/dashboard');
        $dashboardResponse->assertStatus(302); // Redirection vers profile setup
    }

    /** @test */
    public function student_can_complete_profile_setup()
    {
        // Création d'un étudiant
        $student = User::factory()->create([
            'email' => 'profile.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'profile.student@test.com',
            'password' => 'Password123!'
        ]);

        // Accès à la page de setup du profil
        $setupResponse = $this->get('/student/profile/setup');
        $setupResponse->assertStatus(200);

        // Mise à jour du profil
        $updateResponse = $this->post('/student/profile/update', [
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01'
        ]);

        $updateResponse->assertStatus(302); // Redirection après mise à jour
    }

    /** @test */
    public function student_can_create_application()
    {
        // Création d'un étudiant avec profil complet
        $student = User::factory()->create([
            'email' => 'app.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'app.student@test.com',
            'password' => 'Password123!'
        ]);

        // Accès à la page de création de candidature
        $createResponse = $this->get('/student/application/create');
        $createResponse->assertStatus(200);

        // Création d'une candidature
        $response = $this->post('/student/application/create', [
            'university_name' => 'Harvard University',
            'country' => 'United States',
            'program' => 'Computer Science',
            'priority_level' => 'high',
            'estimated_completion_date' => '2025-06-01'
        ]);

        $response->assertStatus(302); // Redirection après création
    }

    /** @test */
    public function student_can_view_application()
    {
        // Création d'un étudiant avec profil complet et candidature
        $student = User::factory()->create([
            'email' => 'view.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'university_name' => 'MIT',
            'country' => 'United States',
            'program' => 'Engineering',
            'status' => 'pending'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'view.student@test.com',
            'password' => 'Password123!'
        ]);

        // Vérification de la candidature
        $response = $this->get('/student/application');
        $response->assertStatus(200);
        $response->assertSee('MIT');
        $response->assertSee('Engineering');
    }

    /** @test */
    public function student_can_upload_document()
    {
        // Création d'un étudiant avec profil complet et candidature
        $student = User::factory()->create([
            'email' => 'doc.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'university_name' => 'Stanford University',
            'country' => 'United States',
            'program' => 'Business Administration'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'doc.student@test.com',
            'password' => 'Password123!'
        ]);

        // Accès à la page des documents
        $documentsResponse = $this->get('/student/documents');
        $documentsResponse->assertStatus(200);

        // Upload d'un document
        $file = UploadedFile::fake()->create('cv.pdf', 100);
        $uploadResponse = $this->post('/student/documents/upload', [
            'application_id' => $application->id,
            'document' => $file,
            'document_type' => 'cv'
        ]);

        $uploadResponse->assertStatus(302); // Redirection après upload
    }

    /** @test */
    public function student_can_send_message()
    {
        // Création d'un étudiant avec profil complet et candidature
        $student = User::factory()->create([
            'email' => 'msg.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'university_name' => 'University of Oxford',
            'country' => 'United Kingdom',
            'program' => 'Law'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'msg.student@test.com',
            'password' => 'Password123!'
        ]);

        // Accès à la page des messages
        $messagesResponse = $this->get('/student/messages');
        $messagesResponse->assertStatus(200);

        // Envoi d'un message
        $sendResponse = $this->post('/student/messages/send', [
            'application_id' => $application->id,
            'content' => 'Bonjour, je suis intéressé par votre programme.'
        ]);

        $sendResponse->assertStatus(302); // Redirection après envoi
    }

    // ========================================
    // TESTS D'ACCEPTATION ADMIN
    // ========================================

    /** @test */
    public function admin_can_login_and_access_dashboard()
    {
        // Création d'un administrateur
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // Connexion admin
        $loginResponse = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'Admin123!'
        ]);

        $loginResponse->assertStatus(302);

        // Accès au dashboard admin
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_all_users()
    {
        // Création d'un administrateur
        $admin = User::factory()->create([
            'email' => 'admin.users@test.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // Création de quelques utilisateurs de test
        User::factory()->create(['email' => 'user1@test.com', 'role' => 'student']);
        User::factory()->create(['email' => 'user2@test.com', 'role' => 'student']);

        // Connexion admin
        $this->post('/login', [
            'email' => 'admin.users@test.com',
            'password' => 'Admin123!'
        ]);

        // Consultation de tous les utilisateurs
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('user1@test.com');
        $response->assertSee('user2@test.com');
    }

    /** @test */
    public function admin_can_view_all_applications()
    {
        // Création d'un administrateur
        $admin = User::factory()->create([
            'email' => 'admin.apps@test.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // Création de quelques candidatures de test
        $student1 = User::factory()->create(['email' => 'student1@test.com', 'role' => 'student']);
        $student2 = User::factory()->create(['email' => 'student2@test.com', 'role' => 'student']);

        Application::factory()->create([
            'user_id' => $student1->id,
            'university_name' => 'Harvard University',
            'country' => 'United States',
            'program' => 'Computer Science'
        ]);

        Application::factory()->create([
            'user_id' => $student2->id,
            'university_name' => 'MIT',
            'country' => 'United States',
            'program' => 'Engineering'
        ]);

        // Connexion admin
        $this->post('/login', [
            'email' => 'admin.apps@test.com',
            'password' => 'Admin123!'
        ]);

        // Consultation de toutes les candidatures
        $response = $this->get('/admin/applications');
        $response->assertStatus(200);
        $response->assertSee('Harvard University');
        $response->assertSee('MIT');
    }

    /** @test */
    public function admin_can_update_application_status()
    {
        // Création d'un administrateur
        $admin = User::factory()->create([
            'email' => 'admin.status@test.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // Création d'une candidature
        $student = User::factory()->create(['email' => 'status.student@test.com', 'role' => 'student']);
        $application = Application::factory()->create([
            'user_id' => $student->id,
            'university_name' => 'Stanford University',
            'status' => 'pending'
        ]);

        // Connexion admin
        $this->post('/login', [
            'email' => 'admin.status@test.com',
            'password' => 'Admin123!'
        ]);

        // Mise à jour du statut
        $updateResponse = $this->put("/admin/applications/{$application->id}/status", [
            'status' => 'approved'
        ]);

        $updateResponse->assertStatus(302); // Redirection après mise à jour

        // Vérification du nouveau statut
        $application->refresh();
        $this->assertEquals('approved', $application->status);
    }

    // ========================================
    // TESTS DE NAVIGATION
    // ========================================

    /** @test */
    public function student_can_navigate_between_pages()
    {
        // Création d'un étudiant avec profil complet
        $student = User::factory()->create([
            'email' => 'nav.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'nav.student@test.com',
            'password' => 'Password123!'
        ]);

        // Test de navigation entre les pages principales
        $pages = [
            '/student/dashboard' => 'Dashboard',
            '/student/application' => 'Application',
            '/student/documents' => 'Documents',
            '/student/messages' => 'Messages'
        ];

        foreach ($pages as $url => $expectedContent) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee($expectedContent);
        }
    }

    /** @test */
    public function unauthorized_access_redirects_to_login()
    {
        // Tentative d'accès sans connexion
        $protectedPages = ['/student/dashboard', '/admin/dashboard'];

        foreach ($protectedPages as $page) {
            $response = $this->get($page);
            $response->assertStatus(302); // Redirection vers login
        }
    }

    /** @test */
    public function student_cannot_access_admin_pages()
    {
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'student.admin@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'student.admin@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative d'accès aux pages admin
        $adminPages = ['/admin/dashboard', '/admin/users', '/admin/applications'];

        foreach ($adminPages as $page) {
            $response = $this->get($page);
            $response->assertStatus(403); // Forbidden
        }
    }

    // ========================================
    // TESTS D'ERREURS
    // ========================================

    /** @test */
    public function invalid_login_credentials_are_handled()
    {
        // Tentative de connexion avec des identifiants incorrects
        $response = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'WrongPassword'
        ]);

        $response->assertStatus(302); // Redirection avec erreur
    }

    /** @test */
    public function invalid_application_data_is_handled()
    {
        // Création d'un étudiant avec profil complet
        $student = User::factory()->create([
            'email' => 'error.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'error.student@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative de création de candidature avec données invalides
        $response = $this->post('/student/application/create', [
            'university_name' => '', // Nom d'université vide
            'country' => '', // Pays vide
        ]);

        $response->assertStatus(302); // Redirection avec erreur de validation
    }

    /** @test */
    public function logout_works_correctly()
    {
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'logout.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'logout.student@test.com',
            'password' => 'Password123!'
        ]);

        // Déconnexion
        $logoutResponse = $this->get('/logout');
        $logoutResponse->assertStatus(302);

        // Vérification que l'utilisateur est déconnecté
        $dashboardResponse = $this->get('/student/dashboard');
        $dashboardResponse->assertStatus(302); // Redirection vers login
    }

    // ========================================
    // TESTS DE PERFORMANCE UX
    // ========================================

    /** @test */
    public function pages_load_within_acceptable_time()
    {
        // Création d'un étudiant avec profil complet
        $student = User::factory()->create([
            'email' => 'perf.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student',
            'phone' => '0123456789',
            'address' => '123 Test Street',
            'date_of_birth' => '1990-01-01',
            'profile_completed' => true
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'perf.student@test.com',
            'password' => 'Password123!'
        ]);

        // Test de temps de chargement des pages principales
        $pages = ['/student/dashboard', '/student/application', '/student/documents', '/student/messages'];

        foreach ($pages as $page) {
            $startTime = microtime(true);
            $response = $this->get($page);
            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            $this->assertLessThan(1000, $loadTime, "Page {$page} took {$loadTime}ms to load");
        }
    }

    // ========================================
    // TESTS DE FLUX COMPLET
    // ========================================

    /** @test */
    public function complete_student_journey()
    {
        // 1. Inscription
        $registerResponse = $this->post('/register', [
            'name' => 'Complete Student',
            'email' => 'complete@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);
        $registerResponse->assertStatus(302);

        // 2. Connexion
        $loginResponse = $this->post('/login', [
            'email' => 'complete@test.com',
            'password' => 'Password123!'
        ]);
        $loginResponse->assertStatus(302);

        // 3. Setup du profil
        $profileResponse = $this->post('/student/profile/update', [
            'phone' => '0123456789',
            'address' => '123 Complete Street',
            'date_of_birth' => '1995-01-01'
        ]);
        $profileResponse->assertStatus(302);

        // 4. Création de candidature
        $appResponse = $this->post('/student/application/create', [
            'university_name' => 'University of Cambridge',
            'country' => 'United Kingdom',
            'program' => 'Mathematics',
            'priority_level' => 'high',
            'estimated_completion_date' => '2025-09-01'
        ]);
        $appResponse->assertStatus(302);

        // 5. Vérification de la candidature
        $viewResponse = $this->get('/student/application');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('University of Cambridge');

        // 6. Déconnexion
        $logoutResponse = $this->get('/logout');
        $logoutResponse->assertStatus(302);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SimpleUserAcceptanceTest extends TestCase
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

        // 3. Vérification accès dashboard
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    /** @test */
    public function student_can_create_application()
    {
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'app.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'app.student@test.com',
            'password' => 'Password123!'
        ]);

        // Création d'une candidature
        $response = $this->post('/applications', [
            'title' => 'Stage Développeur',
            'company' => 'TechCorp',
            'position' => 'Développeur Full-Stack',
            'description' => 'Stage de 6 mois',
            'status' => 'pending'
        ]);

        $response->assertStatus(302); // Redirection après création

        // Vérification que la candidature est visible
        $listResponse = $this->get('/applications');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Stage Développeur');
    }

    /** @test */
    public function student_can_view_applications()
    {
        // Création d'un étudiant avec des candidatures
        $student = User::factory()->create([
            'email' => 'view.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Création de candidatures de test
        Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Marketing',
            'status' => 'pending'
        ]);

        Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Communication',
            'status' => 'accepted'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'view.student@test.com',
            'password' => 'Password123!'
        ]);

        // Vérification de la liste des candidatures
        $response = $this->get('/applications');
        $response->assertStatus(200);
        $response->assertSee('Stage Marketing');
        $response->assertSee('Stage Communication');
    }

    /** @test */
    public function student_can_upload_document()
    {
        // Création d'un étudiant avec une candidature
        $student = User::factory()->create([
            'email' => 'doc.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Finance'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'doc.student@test.com',
            'password' => 'Password123!'
        ]);

        // Upload d'un document
        $file = UploadedFile::fake()->create('cv.pdf', 100);
        $response = $this->post('/documents', [
            'application_id' => $application->id,
            'document' => $file,
            'type' => 'cv'
        ]);

        $response->assertStatus(302); // Redirection après upload

        // Vérification que le document est visible
        $documentsResponse = $this->get('/documents');
        $documentsResponse->assertStatus(200);
        $documentsResponse->assertSee('cv.pdf');
    }

    /** @test */
    public function student_can_send_message()
    {
        // Création d'un étudiant avec une candidature
        $student = User::factory()->create([
            'email' => 'msg.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage IT'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'msg.student@test.com',
            'password' => 'Password123!'
        ]);

        // Envoi d'un message
        $response = $this->post('/messages', [
            'application_id' => $application->id,
            'content' => 'Bonjour, je suis intéressé par votre offre.'
        ]);

        $response->assertStatus(302); // Redirection après envoi

        // Vérification que le message est visible
        $messagesResponse = $this->get('/messages');
        $messagesResponse->assertStatus(200);
        $messagesResponse->assertSee('Bonjour, je suis intéressé');
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
            'title' => 'Stage Marketing'
        ]);

        Application::factory()->create([
            'user_id' => $student2->id,
            'title' => 'Stage Communication'
        ]);

        // Connexion admin
        $this->post('/login', [
            'email' => 'admin.apps@test.com',
            'password' => 'Admin123!'
        ]);

        // Consultation de toutes les candidatures
        $response = $this->get('/admin/applications');
        $response->assertStatus(200);
        $response->assertSee('Stage Marketing');
        $response->assertSee('Stage Communication');
    }

    // ========================================
    // TESTS DE NAVIGATION
    // ========================================

    /** @test */
    public function student_can_navigate_between_pages()
    {
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'nav.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'nav.student@test.com',
            'password' => 'Password123!'
        ]);

        // Test de navigation entre les pages principales
        $pages = [
            '/dashboard' => 'Dashboard',
            '/applications' => 'Applications',
            '/documents' => 'Documents',
            '/messages' => 'Messages'
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
        $protectedPages = ['/dashboard', '/applications', '/documents', '/messages'];

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
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'error.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'error.student@test.com',
            'password' => 'Password123!'
        ]);

        // Tentative de création de candidature avec données invalides
        $response = $this->post('/applications', [
            'title' => '', // Titre vide
            'company' => '', // Entreprise vide
        ]);

        $response->assertStatus(422); // Validation error
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
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertStatus(302);

        // Vérification que l'utilisateur est déconnecté
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(302); // Redirection vers login
    }

    // ========================================
    // TESTS DE PERFORMANCE UX
    // ========================================

    /** @test */
    public function pages_load_within_acceptable_time()
    {
        // Création et connexion d'un étudiant
        $student = User::factory()->create([
            'email' => 'perf.student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'perf.student@test.com',
            'password' => 'Password123!'
        ]);

        // Test de temps de chargement des pages principales
        $pages = ['/dashboard', '/applications', '/documents', '/messages'];

        foreach ($pages as $page) {
            $startTime = microtime(true);
            $response = $this->get($page);
            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            $this->assertLessThan(1000, $loadTime, "Page {$page} took {$loadTime}ms to load");
        }
    }
}

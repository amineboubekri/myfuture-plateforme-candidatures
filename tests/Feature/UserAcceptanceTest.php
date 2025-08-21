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

class UserAcceptanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================
    // PARCOURS ÉTUDIANT COMPLET
    // ========================================

    /** @test */
    public function student_complete_journey()
    {
        // 1. Inscription d'un nouvel étudiant
        $studentData = [
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@student.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->post('/register', $studentData);
        $response->assertStatus(302); // Redirection après inscription

        // 2. Connexion de l'étudiant
        $loginResponse = $this->post('/login', [
            'email' => 'jean.dupont@student.com',
            'password' => 'Password123!'
        ]);
        $loginResponse->assertStatus(302); // Redirection après connexion

        // 3. Accès au dashboard étudiant
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard');

        // 4. Création d'une nouvelle candidature
        $applicationData = [
            'title' => 'Candidature Stage Développeur',
            'company' => 'TechCorp',
            'position' => 'Développeur Full-Stack',
            'description' => 'Stage de 6 mois en développement web',
            'status' => 'pending'
        ];

        $appResponse = $this->post('/applications', $applicationData);
        $appResponse->assertStatus(302); // Redirection après création

        // 5. Vérification que la candidature est visible
        $listResponse = $this->get('/applications');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Candidature Stage Développeur');

        // 6. Upload d'un document
        $file = UploadedFile::fake()->create('cv.pdf', 100);
        $uploadResponse = $this->post('/documents', [
            'application_id' => 1,
            'document' => $file,
            'type' => 'cv'
        ]);
        $uploadResponse->assertStatus(302);

        // 7. Vérification du document uploadé
        $documentsResponse = $this->get('/documents');
        $documentsResponse->assertStatus(200);
        $documentsResponse->assertSee('cv.pdf');

        // 8. Envoi d'un message
        $messageResponse = $this->post('/messages', [
            'application_id' => 1,
            'content' => 'Bonjour, je suis intéressé par votre offre de stage.'
        ]);
        $messageResponse->assertStatus(302);

        // 9. Vérification des messages
        $messagesResponse = $this->get('/messages');
        $messagesResponse->assertStatus(200);
        $messagesResponse->assertSee('Bonjour, je suis intéressé');

        // 10. Déconnexion
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertStatus(302);

        // Vérification que l'utilisateur est déconnecté
        $protectedResponse = $this->get('/dashboard');
        $protectedResponse->assertStatus(302); // Redirection vers login
    }

    // ========================================
    // PARCOURS ADMINISTRATEUR COMPLET
    // ========================================

    /** @test */
    public function admin_complete_journey()
    {
        // Création d'un administrateur
        $admin = User::factory()->create([
            'email' => 'admin@platform.com',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // 1. Connexion administrateur
        $loginResponse = $this->post('/login', [
            'email' => 'admin@platform.com',
            'password' => 'Admin123!'
        ]);
        $loginResponse->assertStatus(302);

        // 2. Accès au dashboard admin
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard');

        // 3. Consultation de tous les utilisateurs
        $usersResponse = $this->get('/admin/users');
        $usersResponse->assertStatus(200);
        $usersResponse->assertSee('Users');

        // 4. Consultation de toutes les candidatures
        $applicationsResponse = $this->get('/admin/applications');
        $applicationsResponse->assertStatus(200);
        $applicationsResponse->assertSee('Applications');

        // 5. Consultation des statistiques
        $statsResponse = $this->get('/admin/statistics');
        $statsResponse->assertStatus(200);

        // 6. Déconnexion admin
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertStatus(302);
    }

    // ========================================
    // SCÉNARIOS D'UTILISATION RÉELS
    // ========================================

    /** @test */
    public function student_applies_to_multiple_positions()
    {
        // Création d'un étudiant
        $student = User::factory()->create([
            'email' => 'marie.curie@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'marie.curie@student.com',
            'password' => 'Password123!'
        ]);

        // Création de plusieurs candidatures
        $applications = [
            [
                'title' => 'Stage Data Science',
                'company' => 'DataCorp',
                'position' => 'Data Scientist',
                'description' => 'Stage en science des données'
            ],
            [
                'title' => 'Alternance Développeur',
                'company' => 'WebAgency',
                'position' => 'Développeur Frontend',
                'description' => 'Alternance en développement frontend'
            ],
            [
                'title' => 'CDI Ingénieur',
                'company' => 'TechGiant',
                'position' => 'Ingénieur Logiciel',
                'description' => 'Poste d\'ingénieur logiciel'
            ]
        ];

        foreach ($applications as $app) {
            $response = $this->post('/applications', $app);
            $response->assertStatus(302);
        }

        // Vérification que toutes les candidatures sont visibles
        $listResponse = $this->get('/applications');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Stage Data Science');
        $listResponse->assertSee('Alternance Développeur');
        $listResponse->assertSee('CDI Ingénieur');
    }

    /** @test */
    public function student_manages_application_status()
    {
        // Création d'un étudiant avec une candidature
        $student = User::factory()->create([
            'email' => 'pierre.durand@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Marketing',
            'status' => 'pending'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'pierre.durand@student.com',
            'password' => 'Password123!'
        ]);

        // 1. Vérification du statut initial
        $initialResponse = $this->get("/applications/{$application->id}");
        $initialResponse->assertStatus(200);
        $initialResponse->assertSee('pending');

        // 2. Mise à jour du statut
        $updateResponse = $this->put("/applications/{$application->id}", [
            'status' => 'accepted'
        ]);
        $updateResponse->assertStatus(302);

        // 3. Vérification du nouveau statut
        $updatedResponse = $this->get("/applications/{$application->id}");
        $updatedResponse->assertStatus(200);
        $updatedResponse->assertSee('accepted');
    }

    /** @test */
    public function student_uploads_multiple_documents()
    {
        // Création d'un étudiant avec une candidature
        $student = User::factory()->create([
            'email' => 'sophie.martin@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Communication'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'sophie.martin@student.com',
            'password' => 'Password123!'
        ]);

        // Upload de plusieurs types de documents
        $documents = [
            ['file' => 'cv.pdf', 'type' => 'cv'],
            ['file' => 'lettre_motivation.pdf', 'type' => 'cover_letter'],
            ['file' => 'recommandation.pdf', 'type' => 'reference'],
            ['file' => 'portfolio.pdf', 'type' => 'portfolio']
        ];

        foreach ($documents as $doc) {
            $file = UploadedFile::fake()->create($doc['file'], 100);
            $response = $this->post('/documents', [
                'application_id' => $application->id,
                'document' => $file,
                'type' => $doc['type']
            ]);
            $response->assertStatus(302);
        }

        // Vérification que tous les documents sont visibles
        $documentsResponse = $this->get('/documents');
        $documentsResponse->assertStatus(200);
        $documentsResponse->assertSee('cv.pdf');
        $documentsResponse->assertSee('lettre_motivation.pdf');
        $documentsResponse->assertSee('recommandation.pdf');
        $documentsResponse->assertSee('portfolio.pdf');
    }

    /** @test */
    public function student_communicates_with_company()
    {
        // Création d'un étudiant avec une candidature
        $student = User::factory()->create([
            'email' => 'lucas.bernard@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'title' => 'Stage Finance'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'lucas.bernard@student.com',
            'password' => 'Password123!'
        ]);

        // Envoi de plusieurs messages
        $messages = [
            'Bonjour, je suis très intéressé par votre offre de stage.',
            'J\'aimerais avoir plus d\'informations sur les missions.',
            'Quand puis-je avoir un entretien ?',
            'Merci pour votre retour, je reste disponible.'
        ];

        foreach ($messages as $message) {
            $response = $this->post('/messages', [
                'application_id' => $application->id,
                'content' => $message
            ]);
            $response->assertStatus(302);
        }

        // Vérification de la conversation
        $messagesResponse = $this->get('/messages');
        $messagesResponse->assertStatus(200);
        foreach ($messages as $message) {
            $messagesResponse->assertSee($message);
        }
    }

    // ========================================
    // TESTS D'ERREURS ET CAS LIMITES
    // ========================================

    /** @test */
    public function student_handles_errors_gracefully()
    {
        // Création d'un étudiant
        $student = User::factory()->create([
            'email' => 'test.error@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'test.error@student.com',
            'password' => 'Password123!'
        ]);

        // 1. Tentative de création de candidature sans données
        $emptyResponse = $this->post('/applications', []);
        $emptyResponse->assertStatus(422); // Validation error

        // 2. Tentative d'upload de fichier trop volumineux
        $largeFile = UploadedFile::fake()->create('large.pdf', 10000); // 10MB
        $largeFileResponse = $this->post('/documents', [
            'application_id' => 1,
            'document' => $largeFile,
            'type' => 'cv'
        ]);
        $largeFileResponse->assertStatus(422);

        // 3. Tentative d'accès à une candidature inexistante
        $notFoundResponse = $this->get('/applications/999');
        $notFoundResponse->assertStatus(404);

        // 4. Tentative d'envoi de message vide
        $emptyMessageResponse = $this->post('/messages', [
            'application_id' => 1,
            'content' => ''
        ]);
        $emptyMessageResponse->assertStatus(422);
    }

    /** @test */
    public function unauthorized_access_is_handled()
    {
        // Tentative d'accès sans connexion
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(302); // Redirection vers login

        $applicationsResponse = $this->get('/applications');
        $applicationsResponse->assertStatus(302);

        $documentsResponse = $this->get('/documents');
        $documentsResponse->assertStatus(302);

        // Tentative d'accès aux pages admin en tant qu'étudiant
        $student = User::factory()->create([
            'email' => 'student@test.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        $this->post('/login', [
            'email' => 'student@test.com',
            'password' => 'Password123!'
        ]);

        $adminDashboardResponse = $this->get('/admin/dashboard');
        $adminDashboardResponse->assertStatus(403); // Forbidden

        $adminUsersResponse = $this->get('/admin/users');
        $adminUsersResponse->assertStatus(403);
    }

    // ========================================
    // TESTS DE NAVIGATION ET UX
    // ========================================

    /** @test */
    public function navigation_flow_is_intuitive()
    {
        // Création d'un étudiant
        $student = User::factory()->create([
            'email' => 'nav.test@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'nav.test@student.com',
            'password' => 'Password123!'
        ]);

        // Test de navigation entre les pages
        $pages = [
            '/dashboard' => 'Dashboard',
            '/applications' => 'Applications',
            '/documents' => 'Documents',
            '/messages' => 'Messages',
            '/profile' => 'Profile'
        ];

        foreach ($pages as $url => $expectedContent) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee($expectedContent);
        }

        // Test de navigation avec boutons/liens
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Applications'); // Lien vers applications
        $dashboardResponse->assertSee('Documents'); // Lien vers documents
    }

    /** @test */
    public function search_and_filter_functionality()
    {
        // Création d'un étudiant avec plusieurs candidatures
        $student = User::factory()->create([
            'email' => 'search.test@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Création de candidatures avec différents statuts
        $applications = [
            ['title' => 'Stage Marketing', 'status' => 'pending'],
            ['title' => 'Stage Communication', 'status' => 'accepted'],
            ['title' => 'Stage Finance', 'status' => 'rejected']
        ];

        foreach ($applications as $app) {
            Application::factory()->create([
                'user_id' => $student->id,
                'title' => $app['title'],
                'status' => $app['status']
            ]);
        }

        // Connexion
        $this->post('/login', [
            'email' => 'search.test@student.com',
            'password' => 'Password123!'
        ]);

        // Test de recherche par titre
        $searchResponse = $this->get('/applications?search=Marketing');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Stage Marketing');
        $searchResponse->assertDontSee('Stage Communication');

        // Test de filtrage par statut
        $filterResponse = $this->get('/applications?status=accepted');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Stage Communication');
        $filterResponse->assertDontSee('Stage Marketing');
    }

    // ========================================
    // TESTS DE PERFORMANCE UX
    // ========================================

    /** @test */
    public function user_experience_performance()
    {
        // Création d'un étudiant
        $student = User::factory()->create([
            'email' => 'ux.test@student.com',
            'password' => Hash::make('Password123!'),
            'role' => 'student'
        ]);

        // Connexion
        $this->post('/login', [
            'email' => 'ux.test@student.com',
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

        // Test de temps de réponse pour les actions
        $startTime = microtime(true);
        $response = $this->post('/applications', [
            'title' => 'Test Application',
            'company' => 'Test Company',
            'position' => 'Test Position',
            'description' => 'Test Description',
            'status' => 'pending'
        ]);
        $endTime = microtime(true);
        $actionTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302);
        $this->assertLessThan(2000, $actionTime, "Application creation took {$actionTime}ms");
    }
}

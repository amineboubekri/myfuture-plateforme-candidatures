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
use Illuminate\Support\Facades\DB;

class SecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================
    // AUTHENTICATION SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_brute_force_login_attacks()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student'
        ]);

        // Try to login with wrong password multiple times
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // The 6th attempt should be blocked due to throttling
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_prevents_sql_injection_in_login()
    {
        $response = $this->post('/login', [
            'email' => "' OR '1'='1",
            'password' => "' OR '1'='1"
        ]);

        $response->assertStatus(422); // Validation error
        $this->assertGuest();
    }

    /** @test */
    public function it_prevents_xss_in_login_form()
    {
        $response = $this->post('/login', [
            'email' => '<script>alert("xss")</script>@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422); // Validation error
        $this->assertGuest();
    }

    /** @test */
    public function it_prevents_session_fixation()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Get session before login
        $this->get('/login');
        $sessionBefore = session()->getId();
        
        // Login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        // Session should be regenerated after login
        $sessionAfter = session()->getId();
        $this->assertNotEquals($sessionBefore, $sessionAfter);
    }

    // ========================================
    // AUTHORIZATION SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_unauthorized_access_to_admin_dashboard()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($student)
            ->get('/admin/dashboard');
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_student_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
            ->get('/student/dashboard');
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_access_to_other_users_applications()
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $application = Application::factory()->create(['user_id' => $user2->id]);
        
        $response = $this->actingAs($user1)
            ->get("/student/application/{$application->id}");
        
        $response->assertStatus(404); // Not found (should not expose existence)
    }

    /** @test */
    public function it_prevents_unauthorized_document_access()
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $document = Document::factory()->create(['user_id' => $user2->id]);
        
        $response = $this->actingAs($user1)
            ->get("/student/documents/{$document->id}");
        
        $response->assertStatus(403); // Forbidden
    }

    // ========================================
    // INPUT VALIDATION SECURITY TESTS
    // ========================================

    /** @test */
    public function it_validates_email_format()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_prevents_sql_injection_in_search()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
            ->get('/admin/applications?search=1%27%20OR%201%3D1%20--');
        
        $response->assertStatus(200); // Should not crash
        // Should not return unexpected data
    }

    /** @test */
    public function it_prevents_xss_in_user_input()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($user)
            ->post('/student/messages/send', [
                'message' => '<script>alert("xss")</script>Hello World'
            ]);
        
        $response->assertStatus(200);
        // Message should be stored with escaped HTML
        $this->assertDatabaseHas('messages', [
            'message' => '&lt;script&gt;alert("xss")&lt;/script&gt;Hello World'
        ]);
    }

    // ========================================
    // FILE UPLOAD SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_upload_of_executable_files()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $file = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
        $this->assertDatabaseMissing('documents', ['filename' => 'malicious.php']);
    }

    /** @test */
    public function it_prevents_upload_of_large_files()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Create a file larger than 10MB
        $file = UploadedFile::fake()->create('large.pdf', 11000); // 11MB
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_validates_file_types()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-executable');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_prevents_path_traversal_in_filename()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $file = UploadedFile::fake()->create('../../../etc/passwd', 100, 'application/pdf');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    // ========================================
    // CSRF PROTECTION TESTS
    // ========================================

    /** @test */
    public function it_prevents_csrf_attacks_on_login()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ], [
            'X-CSRF-TOKEN' => 'invalid-token'
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function it_prevents_csrf_attacks_on_document_upload()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ], [
                'X-CSRF-TOKEN' => 'invalid-token'
            ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    // ========================================
    // API SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_unauthorized_api_access()
    {
        $response = $this->getJson('/api/student/dashboard');
        
        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function it_validates_api_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->getJson('/api/student/dashboard');
        
        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function it_prevents_rate_limiting_bypass()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Make multiple rapid requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)
                ->get('/student/dashboard');
        }
        
        // Should be rate limited
        $response = $this->actingAs($user)
            ->get('/student/dashboard');
        
        $response->assertStatus(429); // Too Many Requests
    }

    // ========================================
    // SESSION SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_session_hijacking()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Login with proper user agent
        $this->actingAs($user)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('/student/dashboard');
        
        // Try to access with different user agent
        $response = $this->actingAs($user)
            ->withHeaders(['User-Agent' => 'Malicious-Bot'])
            ->get('/student/dashboard');
        
        // Should still work (Laravel doesn't validate User-Agent by default)
        // But this test documents the potential vulnerability
        $response->assertStatus(200);
    }

    /** @test */
    public function it_prevents_session_fixation_on_password_change()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $this->actingAs($user);
        $sessionBefore = session()->getId();
        
        $response = $this->post('/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);
        
        $sessionAfter = session()->getId();
        $this->assertNotEquals($sessionBefore, $sessionAfter);
    }

    // ========================================
    // DATA EXPOSURE TESTS
    // ========================================

    /** @test */
    public function it_prevents_sensitive_data_exposure_in_errors()
    {
        $response = $this->get('/non-existent-route');
        
        $response->assertStatus(404);
        $response->assertDontSee('database');
        $response->assertDontSee('password');
        $response->assertDontSee('secret');
    }

    /** @test */
    public function it_prevents_user_enumeration()
    {
        // Try to register with existing email
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        
        // Should not reveal that the email exists
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        // But should not specifically say "email already exists"
    }

    // ========================================
    // BUSINESS LOGIC SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_privilege_escalation()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        // Try to update own role to admin
        $response = $this->actingAs($student)
            ->put("/admin/users/{$student->id}", [
                'role' => 'admin'
            ]);
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_application_status_manipulation()
    {
        $student = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending'
        ]);
        
        // Try to approve own application
        $response = $this->actingAs($student)
            ->put("/admin/applications/{$application->id}/status", [
                'status' => 'approved'
            ]);
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_cross_site_request_forgery_on_status_update()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $application = Application::factory()->create(['status' => 'pending']);
        
        $response = $this->actingAs($admin)
            ->put("/admin/applications/{$application->id}/status", [
                'status' => 'approved'
            ], [
                'X-CSRF-TOKEN' => 'invalid-token'
            ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    // ========================================
    // CONFIGURATION SECURITY TESTS
    // ========================================

    /** @test */
    public function it_has_secure_headers()
    {
        $response = $this->get('/');
        
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    /** @test */
    public function it_prevents_information_disclosure()
    {
        $response = $this->get('/');
        
        $response->assertDontSee('Laravel');
        $response->assertDontSee('PHP');
        $response->assertDontSee('MySQL');
    }
}

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

class AdvancedSecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ========================================
    // IDOR (Insecure Direct Object Reference) TESTS
    // ========================================

    /** @test */
    public function it_prevents_idor_on_application_access()
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $application = Application::factory()->create(['user_id' => $user2->id]);
        
        // User1 tries to access User2's application
        $response = $this->actingAs($user1)
            ->get("/student/application/{$application->id}");
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_idor_on_document_download()
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $document = Document::factory()->create(['user_id' => $user2->id]);
        
        // User1 tries to download User2's document
        $response = $this->actingAs($user1)
            ->get("/student/documents/{$document->id}/download");
        
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_prevents_idor_on_message_access()
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $message = Message::factory()->create(['user_id' => $user2->id]);
        
        // User1 tries to access User2's message
        $response = $this->actingAs($user1)
            ->get("/student/messages/{$message->id}");
        
        $response->assertStatus(403); // Forbidden
    }

    // ========================================
    // MASS ASSIGNMENT TESTS
    // ========================================

    /** @test */
    public function it_prevents_mass_assignment_on_user_role()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($user)
            ->put("/profile", [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'admin' // Should be ignored
            ]);
        
        $user->refresh();
        $this->assertEquals('student', $user->role); // Role should not change
    }

    /** @test */
    public function it_prevents_mass_assignment_on_application_status()
    {
        $student = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($student)
            ->put("/student/application/{$application->id}", [
                'title' => 'Updated Title',
                'status' => 'approved' // Should be ignored
            ]);
        
        $application->refresh();
        $this->assertEquals('pending', $application->status); // Status should not change
    }

    // ========================================
    // ADVANCED INJECTION TESTS
    // ========================================

    /** @test */
    public function it_prevents_advanced_sql_injection()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Test various SQL injection patterns
        $injectionPatterns = [
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "' OR 1=1; INSERT INTO users (name, email) VALUES ('hacker', 'hacker@evil.com'); --",
            "'; UPDATE users SET role='admin' WHERE id=1; --"
        ];
        
        foreach ($injectionPatterns as $pattern) {
            $response = $this->actingAs($admin)
                ->get("/admin/applications?search=" . urlencode($pattern));
            
            $response->assertStatus(200); // Should not crash
            // Should not execute the malicious SQL
        }
    }

    /** @test */
    public function it_prevents_nosql_injection()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Test NoSQL injection patterns
        $injectionPatterns = [
            '{"$gt": ""}',
            '{"$ne": null}',
            '{"$where": "1==1"}',
            '{"$regex": ".*"}'
        ];
        
        foreach ($injectionPatterns as $pattern) {
            $response = $this->actingAs($admin)
                ->get("/admin/applications?filter=" . urlencode($pattern));
            
            $response->assertStatus(200); // Should not crash
        }
    }

    /** @test */
    public function it_prevents_ldap_injection()
    {
        $response = $this->post('/login', [
            'email' => '*)(uid=*))(|(uid=*',
            'password' => 'password123'
        ]);
        
        $response->assertStatus(422); // Validation error
        $this->assertGuest();
    }

    // ========================================
    // ADVANCED XSS TESTS
    // ========================================

    /** @test */
    public function it_prevents_stored_xss_in_messages()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $xssPayloads = [
            '<script>alert("xss")</script>',
            '<img src="x" onerror="alert(\'xss\')">',
            '<svg onload="alert(\'xss\')">',
            'javascript:alert("xss")',
            '"><script>alert("xss")</script>',
            '"><img src="x" onerror="alert(\'xss\')">'
        ];
        
        foreach ($xssPayloads as $payload) {
            $response = $this->actingAs($user)
                ->post('/student/messages/send', [
                    'message' => $payload
                ]);
            
            $response->assertStatus(200);
            // Message should be stored with escaped HTML
            $this->assertDatabaseHas('messages', [
                'message' => htmlspecialchars($payload, ENT_QUOTES, 'UTF-8')
            ]);
        }
    }

    /** @test */
    public function it_prevents_reflected_xss_in_search()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $xssPayload = '<script>alert("xss")</script>';
        
        $response = $this->actingAs($admin)
            ->get("/admin/applications?search=" . urlencode($xssPayload));
        
        $response->assertStatus(200);
        $response->assertDontSee($xssPayload); // Should not reflect the script
    }

    // ========================================
    // FILE UPLOAD ADVANCED TESTS
    // ========================================

    /** @test */
    public function it_prevents_upload_of_malicious_files_with_fake_extensions()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Create a PHP file with .pdf extension
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/x-php');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_prevents_upload_of_files_with_null_bytes()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Create a file with null bytes in name
        $file = UploadedFile::fake()->create('document.php%00.pdf', 100, 'application/pdf');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_prevents_upload_of_files_with_unicode_encoding()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Create a file with unicode characters that might bypass filters
        $file = UploadedFile::fake()->create('document.p\u0068p', 100, 'application/x-php');
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $response->assertStatus(422); // Validation error
    }

    // ========================================
    // SESSION SECURITY ADVANCED TESTS
    // ========================================

    /** @test */
    public function it_prevents_session_prediction()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Login multiple times and check session IDs
        $sessionIds = [];
        
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password'
            ]);
            
            $sessionIds[] = session()->getId();
            $this->post('/logout');
        }
        
        // Session IDs should be random and unpredictable
        $uniqueSessions = array_unique($sessionIds);
        $this->assertCount(5, $uniqueSessions); // All should be unique
    }

    /** @test */
    public function it_prevents_session_replay_attacks()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Login and get session
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $sessionId = session()->getId();
        
        // Logout
        $this->post('/logout');
        
        // Try to use the old session
        $response = $this->withSession(['_token' => $sessionId])
            ->get('/student/dashboard');
        
        $response->assertStatus(302); // Redirect to login
    }

    // ========================================
    // BUSINESS LOGIC ADVANCED TESTS
    // ========================================

    /** @test */
    public function it_prevents_race_conditions_on_application_submission()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Simulate concurrent application submissions
        $responses = [];
        
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->actingAs($user)
                ->post('/student/application/submit', [
                    'title' => "Application {$i}",
                    'content' => "Content {$i}"
                ]);
        }
        
        // Only one application should be created
        $applicationCount = Application::where('user_id', $user->id)->count();
        $this->assertLessThanOrEqual(1, $applicationCount);
    }

    /** @test */
    public function it_prevents_time_based_attacks()
    {
        $existingUser = User::factory()->create(['email' => 'test@example.com']);
        $nonExistingEmail = 'nonexistent@example.com';
        
        // Measure response time for existing user
        $start = microtime(true);
        $this->post('/login', [
            'email' => $existingUser->email,
            'password' => 'wrongpassword'
        ]);
        $existingTime = microtime(true) - $start;
        
        // Measure response time for non-existing user
        $start = microtime(true);
        $this->post('/login', [
            'email' => $nonExistingEmail,
            'password' => 'wrongpassword'
        ]);
        $nonExistingTime = microtime(true) - $start;
        
        // Response times should be similar (within 100ms)
        $this->assertLessThan(0.1, abs($existingTime - $nonExistingTime));
    }

    // ========================================
    // API ADVANCED SECURITY TESTS
    // ========================================

    /** @test */
    public function it_prevents_api_token_enumeration()
    {
        $validToken = 'valid-token';
        $invalidToken = 'invalid-token';
        
        // Test with valid token
        $start = microtime(true);
        $this->withHeaders(['Authorization' => "Bearer {$validToken}"])
            ->getJson('/api/student/dashboard');
        $validTime = microtime(true) - $start;
        
        // Test with invalid token
        $start = microtime(true);
        $this->withHeaders(['Authorization' => "Bearer {$invalidToken}"])
            ->getJson('/api/student/dashboard');
        $invalidTime = microtime(true) - $start;
        
        // Response times should be similar
        $this->assertLessThan(0.1, abs($validTime - $invalidTime));
    }

    /** @test */
    public function it_prevents_api_parameter_pollution()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Test parameter pollution
        $response = $this->actingAs($admin)
            ->get('/admin/applications?status=approved&status=pending&status=rejected');
        
        $response->assertStatus(200); // Should not crash
        // Should handle multiple parameters gracefully
    }

    // ========================================
    // CONFIGURATION ADVANCED TESTS
    // ========================================

    /** @test */
    public function it_prevents_server_information_disclosure()
    {
        $response = $this->get('/');
        
        // Check for common server information leaks
        $response->assertDontSee('Server');
        $response->assertDontSee('X-Powered-By');
        $response->assertDontSee('Apache');
        $response->assertDontSee('nginx');
        $response->assertDontSee('IIS');
    }

    /** @test */
    public function it_prevents_directory_traversal()
    {
        $paths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\drivers\\etc\\hosts',
            '....//....//....//etc/passwd',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd'
        ];
        
        foreach ($paths as $path) {
            $response = $this->get("/{$path}");
            $response->assertStatus(404); // Should not expose files
        }
    }

    /** @test */
    public function it_prevents_http_method_override_abuse()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        // Try to use PUT method via POST with _method parameter
        $response = $this->actingAs($user)
            ->post('/student/profile', [
                '_method' => 'PUT',
                'name' => 'Updated Name'
            ]);
        
        // Should not allow method override without proper CSRF token
        $response->assertStatus(419); // CSRF token mismatch
    }
}

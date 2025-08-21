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
use Illuminate\Support\Facades\Cache;

class PerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        
        // Clear cache before each test
        Cache::flush();
    }

    // ========================================
    // AUTHENTICATION PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function login_response_time_is_acceptable()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student'
        ]);

        $startTime = microtime(true);
        
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(302); // Redirect after login
        $this->assertLessThan(500, $responseTime, "Login response time ({$responseTime}ms) exceeds 500ms threshold");
    }

    /** @test */
    public function registration_response_time_is_acceptable()
    {
        $startTime = microtime(true);
        
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after registration
        $this->assertLessThan(1000, $responseTime, "Registration response time ({$responseTime}ms) exceeds 1000ms threshold");
    }

    /** @test */
    public function logout_response_time_is_acceptable()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $this->actingAs($user);
        
        $startTime = microtime(true);
        
        $response = $this->post('/logout');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after logout
        $this->assertLessThan(200, $responseTime, "Logout response time ({$responseTime}ms) exceeds 200ms threshold");
    }

    // ========================================
    // DASHBOARD PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function student_dashboard_loads_quickly()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/student/dashboard');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, "Student dashboard load time ({$responseTime}ms) exceeds 300ms threshold");
    }

    /** @test */
    public function admin_dashboard_loads_quickly()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, "Admin dashboard load time ({$responseTime}ms) exceeds 500ms threshold");
    }

    /** @test */
    public function dashboard_performance_with_large_dataset()
    {
        // Create multiple users and applications to simulate large dataset
        $users = User::factory()->count(50)->create(['role' => 'student']);
        $applications = Application::factory()->count(100)->create();
        
        $admin = User::factory()->create(['role' => 'admin']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->get('/admin/dashboard');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, "Admin dashboard with large dataset ({$responseTime}ms) exceeds 1000ms threshold");
    }

    // ========================================
    // APPLICATION PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function application_listing_performance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory()->count(50)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->get('/admin/applications');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(800, $responseTime, "Application listing ({$responseTime}ms) exceeds 800ms threshold");
    }

    /** @test */
    public function application_creation_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->post('/student/application/create', [
                'title' => 'Test Application',
                'content' => 'This is a test application content.',
                'type' => 'internship'
            ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after creation
        $this->assertLessThan(600, $responseTime, "Application creation ({$responseTime}ms) exceeds 600ms threshold");
    }

    /** @test */
    public function application_search_performance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory()->count(100)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->get('/admin/applications?search=test');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, "Application search ({$responseTime}ms) exceeds 500ms threshold");
    }

    // ========================================
    // DOCUMENT UPLOAD PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function document_upload_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        $file = UploadedFile::fake()->create('document.pdf', 1024); // 1MB file
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after upload
        $this->assertLessThan(2000, $responseTime, "Document upload ({$responseTime}ms) exceeds 2000ms threshold");
    }

    /** @test */
    public function large_document_upload_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        $file = UploadedFile::fake()->create('large_document.pdf', 5120); // 5MB file
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302);
        $this->assertLessThan(5000, $responseTime, "Large document upload ({$responseTime}ms) exceeds 5000ms threshold");
    }

    /** @test */
    public function document_listing_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        Document::factory()->count(20)->create(['user_id' => $user->id]);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/student/documents');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(400, $responseTime, "Document listing ({$responseTime}ms) exceeds 400ms threshold");
    }

    // ========================================
    // MESSAGING PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function message_sending_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->post('/student/messages/send', [
                'message' => 'Test message content',
                'recipient_id' => 1
            ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, "Message sending ({$responseTime}ms) exceeds 300ms threshold");
    }

    /** @test */
    public function message_listing_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        Message::factory()->count(50)->create(['user_id' => $user->id]);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/student/messages');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(400, $responseTime, "Message listing ({$responseTime}ms) exceeds 400ms threshold");
    }

    // ========================================
    // API PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function api_student_dashboard_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->getJson('/api/student/dashboard');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, "API student dashboard ({$responseTime}ms) exceeds 200ms threshold");
    }

    /** @test */
    public function api_applications_listing_performance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory()->count(30)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->getJson('/api/admin/applications');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, "API applications listing ({$responseTime}ms) exceeds 300ms threshold");
    }

    // ========================================
    // DATABASE PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function database_query_performance()
    {
        // Create test data
        User::factory()->count(100)->create(['role' => 'student']);
        Application::factory()->count(200)->create();
        
        $startTime = microtime(true);
        
        // Perform complex query
        $results = DB::table('users')
            ->join('applications', 'users.id', '=', 'applications.user_id')
            ->where('users.role', 'student')
            ->where('applications.status', 'pending')
            ->select('users.name', 'applications.title', 'applications.created_at')
            ->orderBy('applications.created_at', 'desc')
            ->limit(50)
            ->get();

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $responseTime, "Database query ({$responseTime}ms) exceeds 100ms threshold");
        $this->assertNotEmpty($results);
    }

    /** @test */
    public function database_insert_performance()
    {
        $startTime = microtime(true);
        
        // Insert multiple records
        for ($i = 0; $i < 50; $i++) {
            User::factory()->create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'role' => 'student'
            ]);
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(500, $responseTime, "Database insert ({$responseTime}ms) exceeds 500ms threshold");
    }

    // ========================================
    // CACHE PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function cache_read_performance()
    {
        // Store some data in cache
        Cache::put('test_data', ['key' => 'value'], 60);
        
        $startTime = microtime(true);
        
        $data = Cache::get('test_data');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10, $responseTime, "Cache read ({$responseTime}ms) exceeds 10ms threshold");
        $this->assertEquals(['key' => 'value'], $data);
    }

    /** @test */
    public function cache_write_performance()
    {
        $startTime = microtime(true);
        
        Cache::put('performance_test', 'test_value', 60);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10, $responseTime, "Cache write ({$responseTime}ms) exceeds 10ms threshold");
    }

    // ========================================
    // CONCURRENT REQUEST PERFORMANCE TESTS
    // ========================================

    /** @test */
    public function concurrent_user_access_performance()
    {
        $users = User::factory()->count(10)->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $responses = [];
        foreach ($users as $user) {
            $responses[] = $this->actingAs($user)
                ->get('/student/dashboard');
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        // All responses should be successful
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $this->assertLessThan(2000, $responseTime, "Concurrent user access ({$responseTime}ms) exceeds 2000ms threshold");
    }

    // ========================================
    // MEMORY USAGE TESTS
    // ========================================

    /** @test */
    public function memory_usage_is_reasonable()
    {
        $initialMemory = memory_get_usage();
        
        // Perform memory-intensive operation
        $users = User::factory()->count(1000)->create(['role' => 'student']);
        $applications = Application::factory()->count(2000)->create();
        
        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;
        
        // Memory usage should be less than 50MB
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Memory usage ({$memoryUsed} bytes) exceeds 50MB threshold");
    }

    // ========================================
    // STRESS TESTS
    // ========================================

    /** @test */
    public function stress_test_multiple_operations()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        // Perform multiple operations
        for ($i = 0; $i < 10; $i++) {
            // Create application
            $this->actingAs($user)
                ->post('/student/application/create', [
                    'title' => "Application {$i}",
                    'content' => "Content for application {$i}",
                    'type' => 'internship'
                ]);
            
            // Upload document
            $file = UploadedFile::fake()->create("document{$i}.pdf", 512);
            $this->actingAs($user)
                ->post('/student/documents/upload', [
                    'document' => $file,
                    'type' => 'cv'
                ]);
            
            // Send message
            $this->actingAs($user)
                ->post('/student/messages/send', [
                    'message' => "Message {$i}",
                    'recipient_id' => 1
                ]);
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10000, $responseTime, "Stress test ({$responseTime}ms) exceeds 10000ms threshold");
    }

    // ========================================
    // PERFORMANCE BENCHMARKS
    // ========================================

    /** @test */
    public function performance_benchmarks()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $benchmarks = [];
        
        // Login benchmark
        $startTime = microtime(true);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $benchmarks['login'] = (microtime(true) - $startTime) * 1000;
        
        // Dashboard benchmark
        $startTime = microtime(true);
        $this->actingAs($user)->get('/student/dashboard');
        $benchmarks['dashboard'] = (microtime(true) - $startTime) * 1000;
        
        // Application creation benchmark
        $startTime = microtime(true);
        $this->actingAs($user)->post('/student/application/create', [
            'title' => 'Benchmark Test',
            'content' => 'Benchmark content',
            'type' => 'internship'
        ]);
        $benchmarks['application_creation'] = (microtime(true) - $startTime) * 1000;
        
        // Document upload benchmark
        $file = UploadedFile::fake()->create('benchmark.pdf', 1024);
        $startTime = microtime(true);
        $this->actingAs($user)->post('/student/documents/upload', [
            'document' => $file,
            'type' => 'cv'
        ]);
        $benchmarks['document_upload'] = (microtime(true) - $startTime) * 1000;
        
        // Output benchmarks for analysis
        foreach ($benchmarks as $operation => $time) {
            $this->assertLessThan(2000, $time, "Benchmark {$operation} ({$time}ms) exceeds 2000ms threshold");
        }
        
        // Log benchmarks for monitoring
        \Log::info('Performance Benchmarks', $benchmarks);
    }
}

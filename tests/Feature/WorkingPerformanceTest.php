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

class WorkingPerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function homepage_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect to login
        $this->assertLessThan(200, $responseTime, "Homepage load time ({$responseTime}ms) exceeds 200ms threshold");
        
        \Log::info("Homepage performance: {$responseTime}ms");
    }

    /** @test */
    public function login_page_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/login');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(150, $responseTime, "Login page load time ({$responseTime}ms) exceeds 150ms threshold");
        
        \Log::info("Login page performance: {$responseTime}ms");
    }

    /** @test */
    public function registration_page_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/register');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(150, $responseTime, "Registration page load time ({$responseTime}ms) exceeds 150ms threshold");
        
        \Log::info("Registration page performance: {$responseTime}ms");
    }

    /** @test */
    public function login_performance()
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
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after login
        $this->assertLessThan(500, $responseTime, "Login response time ({$responseTime}ms) exceeds 500ms threshold");
        
        \Log::info("Login performance: {$responseTime}ms");
    }

    /** @test */
    public function registration_performance()
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
        
        \Log::info("Registration performance: {$responseTime}ms");
    }

    /** @test */
    public function student_dashboard_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/student/dashboard');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, "Student dashboard load time ({$responseTime}ms) exceeds 300ms threshold");
        
        \Log::info("Student dashboard performance: {$responseTime}ms");
    }

    /** @test */
    public function admin_dashboard_performance()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->get('/admin/dashboard');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(400, $responseTime, "Admin dashboard load time ({$responseTime}ms) exceeds 400ms threshold");
        
        \Log::info("Admin dashboard performance: {$responseTime}ms");
    }

    /** @test */
    public function application_listing_performance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory()->count(10)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->get('/admin/applications');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, "Application listing load time ({$responseTime}ms) exceeds 500ms threshold");
        
        \Log::info("Application listing performance: {$responseTime}ms");
    }

    /** @test */
    public function database_query_performance()
    {
        // Create test data
        User::factory()->count(50)->create(['role' => 'student']);
        Application::factory()->count(100)->create();
        
        $startTime = microtime(true);
        
        // Perform complex query
        $results = \DB::table('users')
            ->join('applications', 'users.id', '=', 'applications.user_id')
            ->where('users.role', 'student')
            ->select('users.name', 'applications.title', 'applications.created_at')
            ->orderBy('applications.created_at', 'desc')
            ->limit(20)
            ->get();

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(100, $responseTime, "Database query ({$responseTime}ms) exceeds 100ms threshold");
        $this->assertNotEmpty($results);
        
        \Log::info("Database query performance: {$responseTime}ms");
    }

    /** @test */
    public function memory_usage_test()
    {
        $initialMemory = memory_get_usage();
        
        // Perform memory-intensive operation
        $users = User::factory()->count(100)->create(['role' => 'student']);
        $applications = Application::factory()->count(200)->create();
        
        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;
        
        // Memory usage should be less than 50MB
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Memory usage ({$memoryUsed} bytes) exceeds 50MB threshold");
        
        \Log::info("Memory usage: " . round($memoryUsed / 1024 / 1024, 2) . "MB");
    }

    /** @test */
    public function concurrent_requests_performance()
    {
        $users = User::factory()->count(5)->create(['role' => 'student']);
        
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

        $this->assertLessThan(2000, $responseTime, "Concurrent requests ({$responseTime}ms) exceeds 2000ms threshold");
        
        \Log::info("Concurrent requests performance: {$responseTime}ms");
    }

    /** @test */
    public function overall_performance_benchmark()
    {
        $user = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $benchmarks = [];
        
        // Test various operations
        $operations = [
            'homepage' => fn() => $this->get('/'),
            'login_page' => fn() => $this->get('/login'),
            'student_dashboard' => fn() => $this->actingAs($user)->get('/student/dashboard'),
            'admin_dashboard' => fn() => $this->actingAs($admin)->get('/admin/dashboard'),
        ];
        
        foreach ($operations as $name => $operation) {
            $startTime = microtime(true);
            $response = $operation();
            $endTime = microtime(true);
            
            $benchmarks[$name] = ($endTime - $startTime) * 1000;
            
            // Basic status check
            $this->assertLessThan(500, $response->getStatusCode(), "Operation {$name} returned error status");
        }
        
        // Log benchmarks
        \Log::info('Performance Benchmarks', $benchmarks);
        
        // Assert all operations are within acceptable limits
        foreach ($benchmarks as $operation => $time) {
            $this->assertLessThan(500, $time, "Operation {$operation} took {$time}ms, exceeding 500ms threshold");
        }
        
        // Calculate average performance
        $averageTime = array_sum($benchmarks) / count($benchmarks);
        \Log::info("Average performance: {$averageTime}ms");
        
        $this->assertLessThan(300, $averageTime, "Average performance ({$averageTime}ms) exceeds 300ms threshold");
    }

    /** @test */
    public function stress_test_basic()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        // Perform multiple operations
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->get('/student/dashboard');
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(5000, $responseTime, "Stress test ({$responseTime}ms) exceeds 5000ms threshold");
        
        \Log::info("Stress test performance: {$responseTime}ms");
    }
}

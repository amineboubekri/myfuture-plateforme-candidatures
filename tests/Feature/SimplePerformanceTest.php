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

class SimplePerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function homepage_loads_quickly()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect to login
        $this->assertLessThan(200, $responseTime, "Homepage load time ({$responseTime}ms) exceeds 200ms threshold");
    }

    /** @test */
    public function login_page_loads_quickly()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/login');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(150, $responseTime, "Login page load time ({$responseTime}ms) exceeds 150ms threshold");
    }

    /** @test */
    public function registration_page_loads_quickly()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/register');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(150, $responseTime, "Registration page load time ({$responseTime}ms) exceeds 150ms threshold");
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
    }

    /** @test */
    public function document_upload_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        $file = UploadedFile::fake()->create('test.pdf', 512); // 512KB file
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->post('/student/documents/upload', [
                'document' => $file,
                'type' => 'cv'
            ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(302); // Redirect after upload
        $this->assertLessThan(2000, $responseTime, "Document upload time ({$responseTime}ms) exceeds 2000ms threshold");
    }

    /** @test */
    public function api_response_performance()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->getJson('/api/student/dashboard');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, "API response time ({$responseTime}ms) exceeds 200ms threshold");
    }

    /** @test */
    public function search_performance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Application::factory()->count(20)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($admin)
            ->get('/admin/applications?search=test');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, "Search response time ({$responseTime}ms) exceeds 300ms threshold");
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
            'api_dashboard' => fn() => $this->actingAs($user)->getJson('/api/student/dashboard'),
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
    }
}

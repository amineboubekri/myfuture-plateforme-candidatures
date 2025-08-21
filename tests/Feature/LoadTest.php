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
use Illuminate\Support\Facades\Queue;

class LoadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Cache::flush();
        
        // Disable queue processing for faster tests
        Queue::fake();
    }

    // ========================================
    // LOAD TESTING SCENARIOS
    // ========================================

    /** @test */
    public function high_concurrent_user_registration()
    {
        $startTime = microtime(true);
        $concurrentUsers = 20;
        $successfulRegistrations = 0;
        
        for ($i = 0; $i < $concurrentUsers; $i++) {
            try {
                $response = $this->post('/register', [
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com",
                    'password' => 'Password123!',
                    'password_confirmation' => 'Password123!'
                ]);
                
                if ($response->getStatusCode() === 302) {
                    $successfulRegistrations++;
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::warning("Registration failed for user {$i}: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        $this->assertGreaterThan($concurrentUsers * 0.8, $successfulRegistrations, 
            "Only {$successfulRegistrations}/{$concurrentUsers} registrations succeeded");
        $this->assertLessThan(10000, $totalTime, 
            "High concurrent registration took {$totalTime}ms, exceeding 10s threshold");
    }

    /** @test */
    public function high_concurrent_login_attempts()
    {
        // Create users first
        $users = [];
        for ($i = 0; $i < 30; $i++) {
            $users[] = User::factory()->create([
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123'),
                'role' => 'student'
            ]);
        }
        
        $startTime = microtime(true);
        $concurrentLogins = 30;
        $successfulLogins = 0;
        
        for ($i = 0; $i < $concurrentLogins; $i++) {
            try {
                $response = $this->post('/login', [
                    'email' => "user{$i}@example.com",
                    'password' => 'password123'
                ]);
                
                if ($response->getStatusCode() === 302) {
                    $successfulLogins++;
                }
            } catch (\Exception $e) {
                \Log::warning("Login failed for user {$i}: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        $this->assertGreaterThan($concurrentLogins * 0.9, $successfulLogins,
            "Only {$successfulLogins}/{$concurrentLogins} logins succeeded");
        $this->assertLessThan(5000, $totalTime,
            "High concurrent login took {$totalTime}ms, exceeding 5s threshold");
    }

    /** @test */
    public function dashboard_load_under_high_traffic()
    {
        // Create large dataset
        $users = User::factory()->count(100)->create(['role' => 'student']);
        $applications = Application::factory()->count(500)->create();
        $documents = Document::factory()->count(200)->create();
        
        $startTime = microtime(true);
        $concurrentRequests = 50;
        $successfulRequests = 0;
        $responseTimes = [];
        
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $user = $users->random();
            
            $requestStart = microtime(true);
            try {
                $response = $this->actingAs($user)
                    ->get('/student/dashboard');
                
                if ($response->getStatusCode() === 200) {
                    $successfulRequests++;
                    $requestEnd = microtime(true);
                    $responseTimes[] = ($requestEnd - $requestStart) * 1000;
                }
            } catch (\Exception $e) {
                \Log::warning("Dashboard request failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgResponseTime = !empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        $this->assertGreaterThan($concurrentRequests * 0.95, $successfulRequests,
            "Only {$successfulRequests}/{$concurrentRequests} dashboard requests succeeded");
        $this->assertLessThan(1000, $avgResponseTime,
            "Average dashboard response time ({$avgResponseTime}ms) exceeds 1000ms threshold");
        $this->assertLessThan(15000, $totalTime,
            "High traffic dashboard test took {$totalTime}ms, exceeding 15s threshold");
    }

    /** @test */
    public function application_creation_under_load()
    {
        $users = User::factory()->count(20)->create(['role' => 'student']);
        
        $startTime = microtime(true);
        $concurrentApplications = 40;
        $successfulCreations = 0;
        $responseTimes = [];
        
        for ($i = 0; $i < $concurrentApplications; $i++) {
            $user = $users->random();
            
            $requestStart = microtime(true);
            try {
                $response = $this->actingAs($user)
                    ->post('/student/application/create', [
                        'title' => "Application {$i}",
                        'content' => "Content for application {$i}",
                        'type' => 'internship'
                    ]);
                
                if ($response->getStatusCode() === 302) {
                    $successfulCreations++;
                    $requestEnd = microtime(true);
                    $responseTimes[] = ($requestEnd - $requestStart) * 1000;
                }
            } catch (\Exception $e) {
                \Log::warning("Application creation failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgResponseTime = !empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        $this->assertGreaterThan($concurrentApplications * 0.9, $successfulCreations,
            "Only {$successfulCreations}/{$concurrentApplications} applications created successfully");
        $this->assertLessThan(800, $avgResponseTime,
            "Average application creation time ({$avgResponseTime}ms) exceeds 800ms threshold");
    }

    /** @test */
    public function file_upload_under_load()
    {
        $users = User::factory()->count(10)->create(['role' => 'student']);
        
        $startTime = microtime(true);
        $concurrentUploads = 20;
        $successfulUploads = 0;
        $responseTimes = [];
        
        for ($i = 0; $i < $concurrentUploads; $i++) {
            $user = $users->random();
            $file = UploadedFile::fake()->create("document{$i}.pdf", 1024);
            
            $requestStart = microtime(true);
            try {
                $response = $this->actingAs($user)
                    ->post('/student/documents/upload', [
                        'document' => $file,
                        'type' => 'cv'
                    ]);
                
                if ($response->getStatusCode() === 302) {
                    $successfulUploads++;
                    $requestEnd = microtime(true);
                    $responseTimes[] = ($requestEnd - $requestStart) * 1000;
                }
            } catch (\Exception $e) {
                \Log::warning("File upload failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgResponseTime = !empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        $this->assertGreaterThan($concurrentUploads * 0.8, $successfulUploads,
            "Only {$successfulUploads}/{$concurrentUploads} uploads succeeded");
        $this->assertLessThan(3000, $avgResponseTime,
            "Average upload time ({$avgResponseTime}ms) exceeds 3000ms threshold");
    }

    // ========================================
    // STRESS TESTING SCENARIOS
    // ========================================

    /** @test */
    public function extreme_database_load()
    {
        $startTime = microtime(true);
        
        // Create massive dataset
        $users = User::factory()->count(1000)->create(['role' => 'student']);
        $applications = Application::factory()->count(5000)->create();
        $documents = Document::factory()->count(2000)->create();
        $messages = Message::factory()->count(3000)->create();
        
        $endTime = microtime(true);
        $creationTime = ($endTime - $startTime) * 1000;
        
        // Test complex queries on large dataset
        $queryStart = microtime(true);
        
        $results = DB::table('users')
            ->join('applications', 'users.id', '=', 'applications.user_id')
            ->join('documents', 'users.id', '=', 'documents.user_id')
            ->where('users.role', 'student')
            ->where('applications.status', 'pending')
            ->select('users.name', 'applications.title', 'documents.document_type')
            ->orderBy('applications.created_at', 'desc')
            ->limit(100)
            ->get();
        
        $queryEnd = microtime(true);
        $queryTime = ($queryEnd - $queryStart) * 1000;
        
        $this->assertLessThan(10000, $creationTime,
            "Large dataset creation took {$creationTime}ms, exceeding 10s threshold");
        $this->assertLessThan(500, $queryTime,
            "Complex query on large dataset took {$queryTime}ms, exceeding 500ms threshold");
        $this->assertNotEmpty($results);
    }

    /** @test */
    public function memory_stress_test()
    {
        $initialMemory = memory_get_usage();
        $peakMemory = $initialMemory;
        
        // Perform memory-intensive operations
        for ($i = 0; $i < 10; $i++) {
            $users = User::factory()->count(100)->create(['role' => 'student']);
            $applications = Application::factory()->count(200)->create();
            
            $currentMemory = memory_get_usage();
            $peakMemory = max($peakMemory, $currentMemory);
            
            // Force garbage collection
            if ($i % 3 === 0) {
                gc_collect_cycles();
            }
        }
        
        $finalMemory = memory_get_usage();
        $totalMemoryUsed = $peakMemory - $initialMemory;
        $finalMemoryUsed = $finalMemory - $initialMemory;
        
        $this->assertLessThan(100 * 1024 * 1024, $totalMemoryUsed,
            "Peak memory usage ({$totalMemoryUsed} bytes) exceeds 100MB threshold");
        $this->assertLessThan(50 * 1024 * 1024, $finalMemoryUsed,
            "Final memory usage ({$finalMemoryUsed} bytes) exceeds 50MB threshold");
    }

    /** @test */
    public function cache_stress_test()
    {
        $startTime = microtime(true);
        $cacheOperations = 1000;
        $successfulOperations = 0;
        
        for ($i = 0; $i < $cacheOperations; $i++) {
            try {
                $key = "stress_test_{$i}";
                $value = "value_{$i}_" . str_repeat('x', 1000); // 1KB value
                
                Cache::put($key, $value, 60);
                $retrieved = Cache::get($key);
                
                if ($retrieved === $value) {
                    $successfulOperations++;
                }
            } catch (\Exception $e) {
                \Log::warning("Cache operation failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        
        $this->assertGreaterThan($cacheOperations * 0.95, $successfulOperations,
            "Only {$successfulOperations}/{$cacheOperations} cache operations succeeded");
        $this->assertLessThan(5000, $totalTime,
            "Cache stress test took {$totalTime}ms, exceeding 5s threshold");
    }

    // ========================================
    // ENDURANCE TESTING
    // ========================================

    /** @test */
    public function sustained_load_test()
    {
        $users = User::factory()->count(50)->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $startTime = microtime(true);
        $totalOperations = 200;
        $successfulOperations = 0;
        $responseTimes = [];
        
        for ($i = 0; $i < $totalOperations; $i++) {
            $operationStart = microtime(true);
            
            try {
                // Simulate mixed operations
                switch ($i % 4) {
                    case 0:
                        // Dashboard access
                        $user = $users->random();
                        $response = $this->actingAs($user)->get('/student/dashboard');
                        break;
                    case 1:
                        // Application creation
                        $user = $users->random();
                        $response = $this->actingAs($user)->post('/student/application/create', [
                            'title' => "Sustained App {$i}",
                            'content' => "Content {$i}",
                            'type' => 'internship'
                        ]);
                        break;
                    case 2:
                        // Admin dashboard
                        $response = $this->actingAs($admin)->get('/admin/dashboard');
                        break;
                    case 3:
                        // File upload
                        $user = $users->random();
                        $file = UploadedFile::fake()->create("sustained{$i}.pdf", 512);
                        $response = $this->actingAs($user)->post('/student/documents/upload', [
                            'document' => $file,
                            'type' => 'cv'
                        ]);
                        break;
                }
                
                if ($response->getStatusCode() < 500) {
                    $successfulOperations++;
                    $operationEnd = microtime(true);
                    $responseTimes[] = ($operationEnd - $operationStart) * 1000;
                }
            } catch (\Exception $e) {
                \Log::warning("Sustained load operation failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $avgResponseTime = !empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        $this->assertGreaterThan($totalOperations * 0.9, $successfulOperations,
            "Only {$successfulOperations}/{$totalOperations} sustained operations succeeded");
        $this->assertLessThan(1000, $avgResponseTime,
            "Average sustained operation time ({$avgResponseTime}ms) exceeds 1000ms threshold");
        $this->assertLessThan(300000, $totalTime, // 5 minutes
            "Sustained load test took {$totalTime}ms, exceeding 5 minute threshold");
    }

    // ========================================
    // PERFORMANCE MONITORING
    // ========================================

    /** @test */
    public function performance_metrics_collection()
    {
        $metrics = [
            'database_queries' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => [],
            'response_times' => []
        ];
        
        // Enable query logging
        DB::enableQueryLog();
        
        $users = User::factory()->count(10)->create(['role' => 'student']);
        
        foreach ($users as $user) {
            $memoryBefore = memory_get_usage();
            $startTime = microtime(true);
            
            // Perform operations
            $this->actingAs($user)->get('/student/dashboard');
            $this->actingAs($user)->post('/student/application/create', [
                'title' => 'Metrics Test',
                'content' => 'Test content',
                'type' => 'internship'
            ]);
            
            $endTime = microtime(true);
            $memoryAfter = memory_get_usage();
            
            $metrics['response_times'][] = ($endTime - $startTime) * 1000;
            $metrics['memory_usage'][] = $memoryAfter - $memoryBefore;
        }
        
        $metrics['database_queries'] = count(DB::getQueryLog());
        
        // Calculate statistics
        $avgResponseTime = array_sum($metrics['response_times']) / count($metrics['response_times']);
        $maxResponseTime = max($metrics['response_times']);
        $avgMemoryUsage = array_sum($metrics['memory_usage']) / count($metrics['memory_usage']);
        
        // Assertions
        $this->assertLessThan(500, $avgResponseTime,
            "Average response time ({$avgResponseTime}ms) exceeds 500ms threshold");
        $this->assertLessThan(1000, $maxResponseTime,
            "Maximum response time ({$maxResponseTime}ms) exceeds 1000ms threshold");
        $this->assertLessThan(10 * 1024 * 1024, $avgMemoryUsage,
            "Average memory usage ({$avgMemoryUsage} bytes) exceeds 10MB threshold");
        
        // Log metrics for analysis
        \Log::info('Performance Metrics', $metrics);
    }

    /** @test */
    public function system_resource_monitoring()
    {
        $initialMemory = memory_get_usage();
        $initialPeakMemory = memory_get_peak_usage();
        
        // Perform intensive operations
        $users = User::factory()->count(100)->create(['role' => 'student']);
        $applications = Application::factory()->count(200)->create();
        
        // Simulate user activity
        foreach ($users->take(20) as $user) {
            $this->actingAs($user)->get('/student/dashboard');
        }
        
        $finalMemory = memory_get_usage();
        $finalPeakMemory = memory_get_peak_usage();
        
        $memoryIncrease = $finalMemory - $initialMemory;
        $peakMemoryIncrease = $finalPeakMemory - $initialPeakMemory;
        
        // Check memory efficiency
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease,
            "Memory increase ({$memoryIncrease} bytes) exceeds 50MB threshold");
        $this->assertLessThan(100 * 1024 * 1024, $peakMemoryIncrease,
            "Peak memory increase ({$peakMemoryIncrease} bytes) exceeds 100MB threshold");
        
        // Log resource usage
        \Log::info('System Resources', [
            'initial_memory' => $initialMemory,
            'final_memory' => $finalMemory,
            'memory_increase' => $memoryIncrease,
            'peak_memory_increase' => $peakMemoryIncrease
        ]);
    }
}

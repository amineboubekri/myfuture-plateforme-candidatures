<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Application;
use App\Models\Document;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Dashboard Administrateur');
    }

    /** @test */
    public function student_can_access_student_dashboard()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
    }

    /** @test */
    public function admin_dashboard_shows_application_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create test data
        Application::factory()->count(5)->create(['status' => 'pending']);
        Application::factory()->count(3)->create(['status' => 'approved']);
        Application::factory()->count(2)->create(['status' => 'rejected']);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('total_applications', 10);
        $response->assertViewHas('pending_applications', 5);
        $response->assertViewHas('approved_applications', 3);
        $response->assertViewHas('rejected_applications', 2);
    }

    /** @test */
    public function admin_dashboard_shows_user_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create test data
        User::factory()->count(8)->create(['role' => 'student']);
        User::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('total_users', 11); // Including the admin user
        $response->assertViewHas('total_students', 8);
        $response->assertViewHas('total_admins', 3); // Including the logged-in admin
    }

    /** @test */
    public function admin_dashboard_shows_urgent_applications()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create urgent applications
        Application::factory()->count(3)->create([
            'priority_level' => 'high',
            'status' => 'pending'
        ]);

        // Create non-urgent applications
        Application::factory()->count(2)->create([
            'priority_level' => 'medium',
            'status' => 'pending'
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('urgent_applications');
        $this->assertEquals(3, $response->viewData('urgent_applications')->count());
    }

    /** @test */
    public function admin_dashboard_shows_applications_by_country()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create applications from different countries
        Application::factory()->count(4)->create(['country' => 'United States']);
        Application::factory()->count(3)->create(['country' => 'United Kingdom']);
        Application::factory()->count(2)->create(['country' => 'Canada']);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('applications_by_country');
        
        $countryData = $response->viewData('applications_by_country');
        $this->assertEquals(4, $countryData->where('country', 'United States')->first()['total']);
        $this->assertEquals(3, $countryData->where('country', 'United Kingdom')->first()['total']);
        $this->assertEquals(2, $countryData->where('country', 'Canada')->first()['total']);
    }

    /** @test */
    public function admin_dashboard_shows_applications_by_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create applications with different statuses
        Application::factory()->count(5)->create(['status' => 'pending']);
        Application::factory()->count(3)->create(['status' => 'approved']);
        Application::factory()->count(2)->create(['status' => 'rejected']);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('applications_by_status');
        
        $statusData = $response->viewData('applications_by_status');
        $this->assertEquals(5, $statusData->where('status', 'pending')->first()['total']);
        $this->assertEquals(3, $statusData->where('status', 'approved')->first()['total']);
        $this->assertEquals(2, $statusData->where('status', 'rejected')->first()['total']);
    }

    /** @test */
    public function admin_dashboard_shows_recent_user_activity()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create recent users
        $recentUsers = User::factory()->count(5)->create([
            'updated_at' => now()
        ]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('recent_user_activity');
        $this->assertEquals(5, count($response->viewData('recent_user_activity')));
    }

    /** @test */
    public function admin_dashboard_shows_chart_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('chart_data');
        
        $chartData = $response->viewData('chart_data');
        $this->assertArrayHasKey('labels', $chartData);
        $this->assertArrayHasKey('data', $chartData);
    }

    /** @test */
    public function admin_dashboard_shows_conversion_rate()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create applications
        Application::factory()->count(10)->create(['status' => 'pending']);
        Application::factory()->count(5)->create(['status' => 'approved']);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('conversion_rate');
        
        // 5 approved out of 15 total = 33.3%
        $this->assertEquals(33.3, $response->viewData('conversion_rate'));
    }

    /** @test */
    public function admin_dashboard_shows_approval_rate()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create students
        User::factory()->count(10)->create(['role' => 'student', 'is_approved' => true]);
        User::factory()->count(5)->create(['role' => 'student', 'is_approved' => false]);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('approval_rate');
        
        // 10 approved out of 15 students = 66.7%
        $this->assertEquals(66.7, $response->viewData('approval_rate'));
    }

    /** @test */
    public function student_dashboard_shows_user_applications()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        // Create applications for this student
        Application::factory()->count(3)->create(['user_id' => $student->id]);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
    }

    /** @test */
    public function student_dashboard_shows_application_status()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $application = Application::factory()->create([
            'user_id' => $student->id,
            'status' => 'pending'
        ]);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee($application->status);
    }

    /** @test */
    public function student_dashboard_shows_document_count()
    {
        $student = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $student->id]);
        $this->actingAs($student);

        // Create documents for this application
        Document::factory()->count(4)->create(['application_id' => $application->id]);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
    }

    /** @test */
    public function student_dashboard_shows_unread_messages()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        // Create unread messages for this student
        Message::factory()->count(3)->create([
            'receiver_id' => $student->id,
            'read_at' => null
        ]);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('student.dashboard');
    }

    /** @test */
    public function admin_can_view_analytics_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'applications',
            'users',
            'documents',
            'messages'
        ]);
    }

    /** @test */
    public function admin_can_clear_dashboard_cache()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/dashboard/clear-cache');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Cache cleared successfully']);
    }

    /** @test */
    public function admin_can_get_performance_metrics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard/performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response_time',
            'memory_usage',
            'database_queries'
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_student_dashboard()
    {
        $response = $this->get('/student/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function student_cannot_access_admin_dashboard()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_analytics_endpoint()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/analytics');

        $response->assertStatus(200);
    }

    /** @test */
    public function student_cannot_access_analytics_endpoint()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/admin/analytics');

        $response->assertStatus(403);
    }

    /** @test */
    public function dashboard_shows_correct_navigation_links()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Candidatures');
        $response->assertSee('Documents');
        $response->assertSee('Messagerie');
        $response->assertSee('Utilisateurs');
        $response->assertSee('Reporting');
    }

    /** @test */
    public function student_dashboard_shows_correct_navigation_links()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Ma candidature');
        $response->assertSee('Documents');
        $response->assertSee('Messagerie');
    }

    /** @test */
    public function dashboard_shows_theme_toggle_button()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('theme-toggle-btn');
    }
}


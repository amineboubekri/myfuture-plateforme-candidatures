<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\User;
use App\Models\Document;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_an_application()
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $applicationData = [
            'user_id' => $user->id,
            'university_name' => 'Harvard University',
            'country' => 'United States',
            'program' => 'Computer Science',
            'status' => 'pending',
            'priority_level' => 'high',
            'estimated_completion_date' => '2025-06-01',
        ];

        $application = Application::create($applicationData);

        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($user->id, $application->user_id);
        $this->assertEquals('Harvard University', $application->university_name);
        $this->assertEquals('United States', $application->country);
        $this->assertEquals('Computer Science', $application->program);
        $this->assertEquals('pending', $application->status);
        $this->assertEquals('high', $application->priority_level);
    }

    /** @test */
    public function it_has_user_relationship()
    {
        $user = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $application->user);
        $this->assertEquals($user->id, $application->user->id);
    }

    /** @test */
    public function it_has_documents_relationship()
    {
        $application = Application::factory()->create();
        $document = Document::factory()->create(['application_id' => $application->id]);

        $this->assertInstanceOf(Document::class, $application->documents->first());
        $this->assertEquals($document->id, $application->documents->first()->id);
    }

    /** @test */
    public function it_has_steps_relationship()
    {
        $application = Application::factory()->create();
        // Note: ApplicationStep model might not exist yet
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $application->steps);
    }

    /** @test */
    public function it_can_update_status()
    {
        $application = Application::factory()->create(['status' => 'pending']);
        
        $application->update(['status' => 'approved']);
        
        $this->assertEquals('approved', $application->fresh()->status);
    }

    /** @test */
    public function it_can_update_priority_level()
    {
        $application = Application::factory()->create(['priority_level' => 'medium']);
        
        $application->update(['priority_level' => 'high']);
        
        $this->assertEquals('high', $application->fresh()->priority_level);
    }

    /** @test */
    public function it_has_fillable_fields()
    {
        $application = new Application();
        $fillable = $application->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('university_name', $fillable);
        $this->assertContains('country', $fillable);
        $this->assertContains('program', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('priority_level', $fillable);
        $this->assertContains('estimated_completion_date', $fillable);
    }

    /** @test */
    public function it_can_get_applications_by_status()
    {
        $pendingApp = Application::factory()->create(['status' => 'pending']);
        $approvedApp = Application::factory()->create(['status' => 'approved']);
        $rejectedApp = Application::factory()->create(['status' => 'rejected']);

        $pendingApplications = Application::where('status', 'pending')->get();
        $approvedApplications = Application::where('status', 'approved')->get();
        $rejectedApplications = Application::where('status', 'rejected')->get();

        $this->assertEquals(1, $pendingApplications->count());
        $this->assertEquals(1, $approvedApplications->count());
        $this->assertEquals(1, $rejectedApplications->count());
    }

    /** @test */
    public function it_can_get_applications_by_priority()
    {
        $highPriority = Application::factory()->create(['priority_level' => 'high']);
        $mediumPriority = Application::factory()->create(['priority_level' => 'medium']);
        $lowPriority = Application::factory()->create(['priority_level' => 'low']);

        $highPriorityApps = Application::where('priority_level', 'high')->get();
        $mediumPriorityApps = Application::where('priority_level', 'medium')->get();
        $lowPriorityApps = Application::where('priority_level', 'low')->get();

        $this->assertEquals(1, $highPriorityApps->count());
        $this->assertEquals(1, $mediumPriorityApps->count());
        $this->assertEquals(1, $lowPriorityApps->count());
    }

    /** @test */
    public function it_can_get_applications_by_country()
    {
        $usApp = Application::factory()->create(['country' => 'United States']);
        $ukApp = Application::factory()->create(['country' => 'United Kingdom']);
        $caApp = Application::factory()->create(['country' => 'Canada']);

        $usApplications = Application::where('country', 'United States')->get();
        $ukApplications = Application::where('country', 'United Kingdom')->get();
        $caApplications = Application::where('country', 'Canada')->get();

        $this->assertEquals(1, $usApplications->count());
        $this->assertEquals(1, $ukApplications->count());
        $this->assertEquals(1, $caApplications->count());
    }

    /** @test */
    public function it_can_get_urgent_applications()
    {
        $urgentApp = Application::factory()->create([
            'priority_level' => 'high',
            'status' => 'pending'
        ]);
        
        $nonUrgentApp = Application::factory()->create([
            'priority_level' => 'medium',
            'status' => 'pending'
        ]);

        $urgentApplications = Application::where('priority_level', 'high')
            ->where('status', '!=', 'completed')
            ->get();

        $this->assertEquals(1, $urgentApplications->count());
        $this->assertEquals($urgentApp->id, $urgentApplications->first()->id);
    }

    /** @test */
    public function it_can_get_recent_applications()
    {
        $oldApp = Application::factory()->create(['created_at' => now()->subDays(10)]);
        $newApp = Application::factory()->create(['created_at' => now()]);

        $recentApplications = Application::orderBy('created_at', 'desc')->get();

        $this->assertEquals($newApp->id, $recentApplications->first()->id);
        $this->assertEquals($oldApp->id, $recentApplications->last()->id);
    }

    /** @test */
    public function it_can_get_applications_with_user_data()
    {
        $user = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $user->id]);

        $applicationWithUser = Application::with('user')->find($application->id);

        $this->assertInstanceOf(User::class, $applicationWithUser->user);
        $this->assertEquals($user->id, $applicationWithUser->user->id);
    }

    /** @test */
    public function it_can_get_applications_with_documents()
    {
        $application = Application::factory()->create();
        $document = Document::factory()->create(['application_id' => $application->id]);

        $applicationWithDocuments = Application::with('documents')->find($application->id);

        $this->assertEquals(1, $applicationWithDocuments->documents->count());
        $this->assertEquals($document->id, $applicationWithDocuments->documents->first()->id);
    }

    /** @test */
    public function it_can_get_applications_with_steps()
    {
        $application = Application::factory()->create();

        $applicationWithSteps = Application::with('steps')->find($application->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $applicationWithSteps->steps);
    }

    /** @test */
    public function it_can_get_total_applications_count()
    {
        Application::factory()->count(5)->create();
        
        $totalCount = Application::count();
        
        $this->assertEquals(5, $totalCount);
    }

    /** @test */
    public function it_can_get_applications_by_status_count()
    {
        Application::factory()->count(3)->create(['status' => 'pending']);
        Application::factory()->count(2)->create(['status' => 'approved']);
        Application::factory()->count(1)->create(['status' => 'rejected']);

        $pendingCount = Application::where('status', 'pending')->count();
        $approvedCount = Application::where('status', 'approved')->count();
        $rejectedCount = Application::where('status', 'rejected')->count();

        $this->assertEquals(3, $pendingCount);
        $this->assertEquals(2, $approvedCount);
        $this->assertEquals(1, $rejectedCount);
    }

    /** @test */
    public function it_can_get_applications_by_country_count()
    {
        Application::factory()->count(4)->create(['country' => 'United States']);
        Application::factory()->count(3)->create(['country' => 'United Kingdom']);
        Application::factory()->count(2)->create(['country' => 'Canada']);

        $usCount = Application::where('country', 'United States')->count();
        $ukCount = Application::where('country', 'United Kingdom')->count();
        $caCount = Application::where('country', 'Canada')->count();

        $this->assertEquals(4, $usCount);
        $this->assertEquals(3, $ukCount);
        $this->assertEquals(2, $caCount);
    }
}

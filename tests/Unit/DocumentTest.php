<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_document()
    {
        $application = Application::factory()->create();
        
        $documentData = [
            'application_id' => $application->id,
            'document_type' => 'transcript',
            'file_path' => 'documents/transcript.pdf',
            'status' => 'pending',
            'comments' => 'Please review this transcript',
            'uploaded_at' => now(),
        ];

        $document = Document::create($documentData);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($application->id, $document->application_id);
        $this->assertEquals('transcript', $document->document_type);
        $this->assertEquals('documents/transcript.pdf', $document->file_path);
        $this->assertEquals('pending', $document->status);
        $this->assertEquals('Please review this transcript', $document->comments);
    }

    /** @test */
    public function it_has_application_relationship()
    {
        $application = Application::factory()->create();
        $document = Document::factory()->create(['application_id' => $application->id]);

        $this->assertInstanceOf(Application::class, $document->application);
        $this->assertEquals($application->id, $document->application->id);
    }

    /** @test */
    public function it_has_user_relationship_through_application()
    {
        $user = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $user->id]);
        $document = Document::factory()->create(['application_id' => $application->id]);

        $this->assertInstanceOf(User::class, $document->user);
        $this->assertEquals($user->id, $document->user->id);
    }

    /** @test */
    public function it_has_validator_relationship()
    {
        $validator = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create(['validated_by' => $validator->id]);

        $this->assertInstanceOf(User::class, $document->validator);
        $this->assertEquals($validator->id, $document->validator->id);
    }

    /** @test */
    public function it_can_update_status()
    {
        $document = Document::factory()->create(['status' => 'pending']);
        
        $document->update(['status' => 'approved']);
        
        $this->assertEquals('approved', $document->fresh()->status);
    }

    /** @test */
    public function it_can_update_validation_status()
    {
        $validator = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create([
            'status' => 'pending',
            'validated_by' => null,
            'validated_at' => null
        ]);
        
        $document->update([
            'status' => 'approved',
            'validated_by' => $validator->id,
            'validated_at' => now()
        ]);
        
        $this->assertEquals('approved', $document->fresh()->status);
        $this->assertEquals($validator->id, $document->fresh()->validated_by);
        $this->assertNotNull($document->fresh()->validated_at);
    }

    /** @test */
    public function it_has_fillable_fields()
    {
        $document = new Document();
        $fillable = $document->getFillable();

        $this->assertContains('application_id', $fillable);
        $this->assertContains('document_type', $fillable);
        $this->assertContains('file_path', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('comments', $fillable);
        $this->assertContains('uploaded_at', $fillable);
        $this->assertContains('validated_at', $fillable);
        $this->assertContains('validated_by', $fillable);
    }

    /** @test */
    public function it_can_get_documents_by_status()
    {
        $pendingDoc = Document::factory()->create(['status' => 'pending']);
        $approvedDoc = Document::factory()->create(['status' => 'approved']);
        $rejectedDoc = Document::factory()->create(['status' => 'rejected']);

        $pendingDocuments = Document::where('status', 'pending')->get();
        $approvedDocuments = Document::where('status', 'approved')->get();
        $rejectedDocuments = Document::where('status', 'rejected')->get();

        $this->assertEquals(1, $pendingDocuments->count());
        $this->assertEquals(1, $approvedDocuments->count());
        $this->assertEquals(1, $rejectedDocuments->count());
    }

    /** @test */
    public function it_can_get_documents_by_type()
    {
        $transcript = Document::factory()->create(['document_type' => 'transcript']);
        $certificate = Document::factory()->create(['document_type' => 'certificate']);
        $passport = Document::factory()->create(['document_type' => 'passport']);

        $transcripts = Document::where('document_type', 'transcript')->get();
        $certificates = Document::where('document_type', 'certificate')->get();
        $passports = Document::where('document_type', 'passport')->get();

        $this->assertEquals(1, $transcripts->count());
        $this->assertEquals(1, $certificates->count());
        $this->assertEquals(1, $passports->count());
    }

    /** @test */
    public function it_can_get_documents_by_application()
    {
        $application1 = Application::factory()->create();
        $application2 = Application::factory()->create();
        
        Document::factory()->count(3)->create(['application_id' => $application1->id]);
        Document::factory()->count(2)->create(['application_id' => $application2->id]);

        $app1Documents = Document::where('application_id', $application1->id)->get();
        $app2Documents = Document::where('application_id', $application2->id)->get();

        $this->assertEquals(3, $app1Documents->count());
        $this->assertEquals(2, $app2Documents->count());
    }

    /** @test */
    public function it_can_get_validated_documents()
    {
        $validator = User::factory()->create(['role' => 'admin']);
        
        $validatedDoc = Document::factory()->create([
            'validated_by' => $validator->id,
            'validated_at' => now()
        ]);
        
        $unvalidatedDoc = Document::factory()->create([
            'validated_by' => null,
            'validated_at' => null
        ]);

        $validatedDocuments = Document::whereNotNull('validated_by')->get();
        $unvalidatedDocuments = Document::whereNull('validated_by')->get();

        $this->assertEquals(1, $validatedDocuments->count());
        $this->assertEquals(1, $unvalidatedDocuments->count());
    }

    /** @test */
    public function it_can_get_recent_documents()
    {
        $oldDoc = Document::factory()->create(['uploaded_at' => now()->subDays(10)]);
        $newDoc = Document::factory()->create(['uploaded_at' => now()]);

        $recentDocuments = Document::orderBy('uploaded_at', 'desc')->get();

        $this->assertEquals($newDoc->id, $recentDocuments->first()->id);
        $this->assertEquals($oldDoc->id, $recentDocuments->last()->id);
    }

    /** @test */
    public function it_can_get_documents_with_application_data()
    {
        $application = Application::factory()->create();
        $document = Document::factory()->create(['application_id' => $application->id]);

        $documentWithApplication = Document::with('application')->find($document->id);

        $this->assertInstanceOf(Application::class, $documentWithApplication->application);
        $this->assertEquals($application->id, $documentWithApplication->application->id);
    }

    /** @test */
    public function it_can_get_documents_with_user_data()
    {
        $user = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $user->id]);
        $document = Document::factory()->create(['application_id' => $application->id]);

        $documentWithUser = Document::with('user')->find($document->id);

        $this->assertInstanceOf(User::class, $documentWithUser->user);
        $this->assertEquals($user->id, $documentWithUser->user->id);
    }

    /** @test */
    public function it_can_get_documents_with_validator_data()
    {
        $validator = User::factory()->create(['role' => 'admin']);
        $document = Document::factory()->create(['validated_by' => $validator->id]);

        $documentWithValidator = Document::with('validator')->find($document->id);

        $this->assertInstanceOf(User::class, $documentWithValidator->validator);
        $this->assertEquals($validator->id, $documentWithValidator->validator->id);
    }

    /** @test */
    public function it_can_get_total_documents_count()
    {
        Document::factory()->count(7)->create();
        
        $totalCount = Document::count();
        
        $this->assertEquals(7, $totalCount);
    }

    /** @test */
    public function it_can_get_documents_by_status_count()
    {
        Document::factory()->count(4)->create(['status' => 'pending']);
        Document::factory()->count(3)->create(['status' => 'approved']);
        Document::factory()->count(2)->create(['status' => 'rejected']);

        $pendingCount = Document::where('status', 'pending')->count();
        $approvedCount = Document::where('status', 'approved')->count();
        $rejectedCount = Document::where('status', 'rejected')->count();

        $this->assertEquals(4, $pendingCount);
        $this->assertEquals(3, $approvedCount);
        $this->assertEquals(2, $rejectedCount);
    }

    /** @test */
    public function it_can_get_documents_by_type_count()
    {
        Document::factory()->count(5)->create(['document_type' => 'transcript']);
        Document::factory()->count(3)->create(['document_type' => 'certificate']);
        Document::factory()->count(2)->create(['document_type' => 'passport']);

        $transcriptCount = Document::where('document_type', 'transcript')->count();
        $certificateCount = Document::where('document_type', 'certificate')->count();
        $passportCount = Document::where('document_type', 'passport')->count();

        $this->assertEquals(5, $transcriptCount);
        $this->assertEquals(3, $certificateCount);
        $this->assertEquals(2, $passportCount);
    }

    /** @test */
    public function it_can_check_if_document_is_validated()
    {
        $validator = User::factory()->create(['role' => 'admin']);
        
        $validatedDoc = Document::factory()->create([
            'validated_by' => $validator->id,
            'validated_at' => now()
        ]);
        
        $unvalidatedDoc = Document::factory()->create([
            'validated_by' => null,
            'validated_at' => null
        ]);

        $this->assertTrue($validatedDoc->validated_by !== null);
        $this->assertTrue($validatedDoc->validated_at !== null);
        $this->assertTrue($unvalidatedDoc->validated_by === null);
        $this->assertTrue($unvalidatedDoc->validated_at === null);
    }
}


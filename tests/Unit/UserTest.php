<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Application;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_approved' => false,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('student', $user->role);
        $this->assertFalse($user->is_approved);
    }

    /** @test */
    public function it_has_applications_relationship()
    {
        $user = User::factory()->create(['role' => 'student']);
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Application::class, $user->applications->first());
        $this->assertEquals($application->id, $user->applications->first()->id);
    }

    /** @test */
    public function it_has_notifications_relationship()
    {
        $user = User::factory()->create();
        // Note: Notifications are typically created by the system, not directly
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->notifications);
    }

    /** @test */
    public function it_can_check_if_profile_is_complete()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'date_of_birth' => '1990-01-01',
        ]);

        $this->assertTrue($user->isProfileComplete());
    }

    /** @test */
    public function it_can_check_if_profile_is_incomplete()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => null,
            'address' => null,
            'date_of_birth' => null,
        ]);

        $this->assertFalse($user->isProfileComplete());
    }

    /** @test */
    public function it_can_update_profile_completion_status()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'date_of_birth' => '1990-01-01',
        ]);

        $user->updateProfileCompletionStatus();

        $this->assertTrue($user->profile_completed);
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->assertTrue($admin->role === 'admin');
        $this->assertFalse($student->role === 'admin');
    }

    /** @test */
    public function it_can_check_if_user_is_student()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->assertTrue($student->role === 'student');
        $this->assertFalse($admin->role === 'student');
    }

    /** @test */
    public function it_can_check_if_2fa_is_enabled()
    {
        $user = User::factory()->create([
            'google2fa_enabled' => true,
            'google2fa_secret' => 'test_secret',
        ]);

        $this->assertTrue($user->google2fa_enabled);
        $this->assertNotNull($user->google2fa_secret);
    }

    /** @test */
    public function it_can_check_if_2fa_is_disabled()
    {
        $user = User::factory()->create([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
        ]);

        $this->assertFalse($user->google2fa_enabled);
        $this->assertNull($user->google2fa_secret);
    }

    /** @test */
    public function it_can_get_user_applications_count()
    {
        $user = User::factory()->create();
        Application::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertEquals(3, $user->applications->count());
    }

    /** @test */
    public function it_can_get_user_notifications_count()
    {
        $user = User::factory()->create();
        // Note: Notifications are typically created by the system, not directly
        $this->assertEquals(0, $user->notifications->count());
    }

    /** @test */
    public function it_has_fillable_fields()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('date_of_birth', $fillable);
    }

    /** @test */
    public function it_has_hidden_fields()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('google2fa_secret', $hidden);
    }

    /** @test */
    public function it_can_be_approved()
    {
        $user = User::factory()->create(['is_approved' => 0]);
        
        $user->update(['is_approved' => 1]);
        
        $this->assertEquals(1, $user->fresh()->is_approved);
    }

    /** @test */
    public function it_can_be_rejected()
    {
        $user = User::factory()->create(['is_approved' => 1]);
        
        $user->update(['is_approved' => 0]);
        
        $this->assertEquals(0, $user->fresh()->is_approved);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function user_can_view_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Connexion');
    }

    /** @test */
    public function user_can_view_register_page()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee('Inscription');
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'student',
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
    }

    /** @test */
    public function user_cannot_register_with_mismatched_passwords()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'student',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'is_approved' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/student/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function user_cannot_login_if_not_approved()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'is_approved' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function admin_can_login_and_access_admin_dashboard()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function student_can_login_and_access_student_dashboard()
    {
        $student = User::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_approved' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/student/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function user_can_view_password_reset_page()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    /** @test */
    public function user_cannot_request_password_reset_with_invalid_email()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_view_password_reset_form()
    {
        $token = 'valid-token';
        $email = 'john@example.com';

        $response = $this->get("/reset-password/{$token}?email={$email}");

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'john@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function user_cannot_reset_password_with_invalid_token()
    {
        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'john@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_view_2fa_setup_page()
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $response = $this->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertViewIs('auth.2fa.setup');
    }

    /** @test */
    public function user_can_enable_2fa()
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->actingAs($user);

        $response = $this->post('/2fa/enable', [
            'secret' => 'test-secret-key',
            'code' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertTrue($user->fresh()->google2fa_enabled);
    }

    /** @test */
    public function user_can_disable_2fa()
    {
        $user = User::factory()->create([
            'role' => 'student',
            'google2fa_enabled' => true,
            'google2fa_secret' => 'test-secret',
        ]);
        $this->actingAs($user);

        $response = $this->post('/2fa/disable', [
            'code' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->google2fa_enabled);
    }

    /** @test */
    public function user_can_view_change_password_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/change-password');

        $response->assertStatus(200);
        $response->assertViewIs('auth.change-password');
    }

    /** @test */
    public function user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    /** @test */
    public function user_cannot_change_password_with_mismatched_new_passwords()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        $this->actingAs($user);

        $response = $this->post('/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_pages()
    {
        $response = $this->get('/student/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/change-password');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function student_cannot_access_admin_pages()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_pages()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }
}


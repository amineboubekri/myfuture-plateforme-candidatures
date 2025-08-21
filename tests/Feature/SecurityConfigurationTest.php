<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class SecurityConfigurationTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // LARAVEL SECURITY CONFIGURATION TESTS
    // ========================================

    /** @test */
    public function it_has_secure_session_configuration()
    {
        $this->assertTrue(Config::get('session.secure'));
        $this->assertTrue(Config::get('session.http_only'));
        $this->assertTrue(Config::get('session.same_site') === 'lax' || Config::get('session.same_site') === 'strict');
    }

    /** @test */
    public function it_has_secure_cookie_configuration()
    {
        $this->assertTrue(Config::get('session.secure'));
        $this->assertTrue(Config::get('session.http_only'));
    }

    /** @test */
    public function it_has_proper_csrf_protection()
    {
        $this->assertTrue(Config::get('session.csrf_protection'));
        $this->assertNotEmpty(Config::get('app.key'));
    }

    /** @test */
    public function it_has_secure_password_configuration()
    {
        $this->assertGreaterThanOrEqual(8, Config::get('auth.password.min'));
        $this->assertTrue(Config::get('auth.password.mixed_case'));
        $this->assertTrue(Config::get('auth.password.uncompromised'));
    }

    /** @test */
    public function it_has_proper_rate_limiting()
    {
        $this->assertArrayHasKey('throttle', Config::get('auth.guards.web.middleware'));
    }

    /** @test */
    public function it_has_secure_headers_middleware()
    {
        $response = $this->get('/');
        
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
    }

    /** @test */
    public function it_prevents_information_disclosure_in_production()
    {
        if (app()->environment('production')) {
            $response = $this->get('/');
            
            $response->assertDontSee('Laravel');
            $response->assertDontSee('PHP');
            $response->assertDontSee('MySQL');
            $response->assertDontSee('database');
        }
    }

    /** @test */
    public function it_has_secure_file_upload_configuration()
    {
        $this->assertLessThanOrEqual(10240, Config::get('filesystems.max_file_size')); // 10MB max
        $this->assertNotEmpty(Config::get('filesystems.allowed_extensions'));
    }

    /** @test */
    public function it_has_proper_logging_configuration()
    {
        $this->assertNotEquals('single', Config::get('logging.default'));
        $this->assertTrue(Config::get('logging.channels.stack.enabled'));
    }

    /** @test */
    public function it_has_secure_database_configuration()
    {
        $this->assertNotEmpty(Config::get('database.connections.mysql.database'));
        $this->assertNotEmpty(Config::get('database.connections.mysql.username'));
        $this->assertNotEmpty(Config::get('database.connections.mysql.password'));
    }

    /** @test */
    public function it_has_proper_cache_configuration()
    {
        $this->assertNotEquals('file', Config::get('cache.default'));
        $this->assertTrue(Config::get('cache.stores.redis.enabled'));
    }

    /** @test */
    public function it_has_secure_mail_configuration()
    {
        $this->assertNotEmpty(Config::get('mail.mailers.smtp.host'));
        $this->assertNotEmpty(Config::get('mail.mailers.smtp.username'));
        $this->assertNotEmpty(Config::get('mail.mailers.smtp.password'));
        $this->assertTrue(Config::get('mail.mailers.smtp.encryption') === 'tls' || Config::get('mail.mailers.smtp.encryption') === 'ssl');
    }

    /** @test */
    public function it_has_proper_validation_configuration()
    {
        $this->assertTrue(Config::get('validation.strict'));
        $this->assertTrue(Config::get('validation.escape_html'));
    }

    /** @test */
    public function it_has_secure_storage_configuration()
    {
        $this->assertNotEquals('public', Config::get('filesystems.default'));
        $this->assertTrue(Config::get('filesystems.disks.local.visibility') === 'private');
    }

    /** @test */
    public function it_has_proper_encryption_configuration()
    {
        $this->assertNotEmpty(Config::get('app.key'));
        $this->assertEquals(32, strlen(base64_decode(Config::get('app.key'))));
    }

    /** @test */
    public function it_has_secure_authentication_configuration()
    {
        $this->assertTrue(Config::get('auth.guards.web.driver') === 'session');
        $this->assertTrue(Config::get('auth.providers.users.driver') === 'eloquent');
    }

    /** @test */
    public function it_has_proper_middleware_configuration()
    {
        $middleware = Config::get('app.middleware');
        
        $this->assertContains('web', $middleware);
        $this->assertContains('api', $middleware);
        $this->assertContains('throttle', $middleware);
    }

    /** @test */
    public function it_has_secure_route_configuration()
    {
        $this->assertTrue(Config::get('routes.cache.enabled'));
        $this->assertTrue(Config::get('routes.cache.ttl') > 0);
    }

    /** @test */
    public function it_has_proper_error_handling_configuration()
    {
        if (app()->environment('production')) {
            $this->assertTrue(Config::get('app.debug') === false);
            $this->assertTrue(Config::get('app.env') === 'production');
        }
    }

    /** @test */
    public function it_has_secure_view_configuration()
    {
        $this->assertTrue(Config::get('view.cache'));
        $this->assertTrue(Config::get('view.compiled'));
    }

    /** @test */
    public function it_has_proper_queue_configuration()
    {
        $this->assertNotEquals('sync', Config::get('queue.default'));
        $this->assertTrue(Config::get('queue.connections.redis.enabled'));
    }

    /** @test */
    public function it_has_secure_broadcasting_configuration()
    {
        $this->assertNotEquals('log', Config::get('broadcasting.default'));
        $this->assertTrue(Config::get('broadcasting.connections.redis.enabled'));
    }

    /** @test */
    public function it_has_proper_notification_configuration()
    {
        $this->assertTrue(Config::get('notifications.channels.mail.enabled'));
        $this->assertTrue(Config::get('notifications.channels.database.enabled'));
    }

    /** @test */
    public function it_has_secure_hashing_configuration()
    {
        $this->assertTrue(Hash::isHashed(Hash::make('password')));
        $this->assertTrue(Config::get('hashing.driver') === 'bcrypt' || Config::get('hashing.driver') === 'argon');
    }

    /** @test */
    public function it_has_proper_cors_configuration()
    {
        $this->assertTrue(Config::get('cors.enabled'));
        $this->assertNotEmpty(Config::get('cors.allowed_origins'));
        $this->assertTrue(Config::get('cors.allowed_methods'));
    }

    /** @test */
    public function it_has_secure_trusted_proxies_configuration()
    {
        $this->assertNotEmpty(Config::get('trustedproxy.proxies'));
        $this->assertTrue(Config::get('trustedproxy.headers'));
    }

    /** @test */
    public function it_has_proper_sanctum_configuration()
    {
        $this->assertTrue(Config::get('sanctum.stateful'));
        $this->assertNotEmpty(Config::get('sanctum.guard'));
        $this->assertTrue(Config::get('sanctum.expiration') > 0);
    }

    /** @test */
    public function it_has_secure_two_factor_configuration()
    {
        $this->assertTrue(Config::get('2fa.enabled'));
        $this->assertNotEmpty(Config::get('2fa.issuer'));
        $this->assertTrue(Config::get('2fa.window') > 0);
    }

    /** @test */
    public function it_has_proper_cache_headers_configuration()
    {
        $response = $this->get('/');
        
        $response->assertHeader('Cache-Control');
        $response->assertHeader('Pragma');
        $response->assertHeader('Expires');
    }

    /** @test */
    public function it_has_secure_content_security_policy()
    {
        $response = $this->get('/');
        
        $response->assertHeader('Content-Security-Policy');
        $response->assertDontSee('unsafe-inline');
        $response->assertDontSee('unsafe-eval');
    }

    /** @test */
    public function it_has_proper_referrer_policy()
    {
        $response = $this->get('/');
        
        $response->assertHeader('Referrer-Policy');
        $response->assertNotEquals('no-referrer-when-downgrade', $response->headers->get('Referrer-Policy'));
    }

    /** @test */
    public function it_has_secure_permissions_policy()
    {
        $response = $this->get('/');
        
        $response->assertHeader('Permissions-Policy');
        $response->assertDontSee('geolocation=()');
        $response->assertDontSee('microphone=()');
        $response->assertDontSee('camera=()');
    }
}

<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    public function test_welcome_is_secure_behind_railway_proxy_headers(): void
    {
        $response = $this->get('/', [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'love-languages.up.railway.app',
            'Host' => 'love-languages.up.railway.app',
        ]);

        $response->assertOk();
        $this->assertTrue($this->app['request']->secure());
        $this->assertSame('https', $this->app['request']->getScheme());
    }

    public function test_session_cookie_is_marked_secure_behind_https_proxy(): void
    {
        $response = $this->post('/assessment/start', [
            'name' => 'Budi Santoso',
        ], [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'love-languages.up.railway.app',
            'Host' => 'love-languages.up.railway.app',
        ]);

        $response->assertRedirect('/assessment');

        $sessionCookie = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($sessionCookie, 'Session cookie should be present.');
        $this->assertTrue($sessionCookie->isSecure(), 'Session cookie should be marked Secure behind an HTTPS proxy.');
    }
}

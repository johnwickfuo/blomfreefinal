<?php

namespace Tests\Feature;

use App\Http\Middleware\EdgeCache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class PhaseOneSecurityTest extends TestCase
{
    public function test_catalogue_responses_are_not_shared_cacheable(): void
    {
        $response = (new EdgeCache)->handle(Request::create('/lands'), fn () => new Response('catalogue'));

        $this->assertSame('private, no-store', $response->headers->get('Cache-Control'));
    }

    public function test_admin_login_is_available_without_a_custom_session_cookie(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}

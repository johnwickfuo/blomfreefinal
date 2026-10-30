<?php

namespace Tests\Feature;

use App\Http\Middleware\EdgeCache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class PhaseOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_responses_are_not_shared_cacheable(): void
    {
        $response = (new EdgeCache)->handle(Request::create('/lands'), fn () => new Response('catalogue'));

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_unsigned_order_and_inspection_links_are_rejected(): void
    {
        $this->get('/orders/unknown-reference')->assertNotFound();
        $this->get('/inspections/unknown-reference')->assertNotFound();
    }

    public function test_customer_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);
        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_admin_login_is_available_without_a_custom_session_cookie(): void
    {
        $this->get('/admin/login')->assertOk();
    }
}

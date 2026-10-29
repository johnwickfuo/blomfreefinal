<?php

namespace Tests\Feature;

use App\Models\Land;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_with_empty_inventory(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('featured.lands', 0)
            ->has('featured.animals', 0)
            ->has('featured.collections', 0)
            ->has('featured.gadgets', 0));
    }
}

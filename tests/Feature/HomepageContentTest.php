<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\HomepageBanner;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_approved_and_active_content_is_visible(): void
    {
        Article::create(['title' => 'Published', 'slug' => 'published', 'category' => 'company', 'excerpt' => 'News', 'body' => 'Body', 'is_published' => true, 'published_at' => now()->subMinute()]);
        Article::create(['title' => 'Draft', 'slug' => 'draft', 'category' => 'company', 'excerpt' => 'News', 'body' => 'Body']);
        Testimonial::create(['customer_name' => 'Approved', 'quote' => 'Good service', 'is_approved' => true]);
        Testimonial::create(['customer_name' => 'Pending', 'quote' => 'Private']);
        HomepageBanner::create(['headline' => 'Active promotion', 'is_active' => true]);
        HomepageBanner::create(['headline' => 'Inactive promotion']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Home')->has('articles', 1)->has('testimonials', 1)->has('banners', 1));
        $this->get('/insights/published')->assertOk();
        $this->get('/insights/draft')->assertNotFound();
    }
}

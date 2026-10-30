<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Land;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhaseFourDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $options = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Wireless Mouse',
            'subsidiary' => 'gadgets',
            'category' => 'Accessories',
            'short_description' => 'Precision wireless mouse for work',
            'description' => 'A portable wireless mouse.',
            'base_price' => 9500,
            'stock' => 20,
            'has_variants' => false,
            'is_published' => true,
        ], $options));
    }

    public function test_search_displays_only_public_items_and_articles(): void
    {
        $this->product(['name' => 'Wireless Keyboard', 'short_description' => 'Desk keyboard']);
        $this->product(['name' => 'Hidden Keyboard', 'is_published' => false]);
        Article::create(['title' => 'Keyboard Buying Guide', 'slug' => 'keyboard-guide',
            'category' => 'gadgets', 'excerpt' => 'Learn about keyboards', 'body' => 'Guide',
            'is_published' => true, 'published_at' => now()->subDay()]);
        Article::create(['title' => 'Keyboard Secret Draft', 'slug' => 'keyboard-draft',
            'category' => 'gadgets', 'excerpt' => 'Draft', 'body' => 'Draft',
            'is_published' => false]);
        $this->get('/search?q=keyboard')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Search/Index')
                ->has('groups.gadgets', 1)->has('groups.articles', 1)
        );
        $this->get('/search?q=keyboard&type=gadgets')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Search/Index')
                ->has('groups.gadgets', 1)->has('groups.articles', 0)
        );
        $this->get('/search?q=a')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Search/Index')->where('hasSearched', false)
        );
    }

    public function test_saved_listings_exclude_drafts_and_validate_ids(): void
    {
        $public = $this->product();
        $private = $this->product(['name' => 'Private Gadget', 'is_published' => false]);
        $this->get('/saved?products='.$public->id.','.$private->id)->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Saved/Index')->has('groups.products', 1)
        );
        $this->get('/saved?products=1%20OR%201=1')->assertStatus(422);
        $this->get('/saved')->assertOk();
    }

    public function test_reviews_require_authentication_and_paid_purchase(): void
    {
        $product = $this->product();
        $payload = ['rating' => 5, 'display_name' => 'Buyer', 'title' => 'Excellent purchase',
            'body' => 'Great product with a comfortable feel and accurate description.'];
        $this->post('/shop/products/'.$product->slug.'/reviews', $payload)->assertRedirect('/login');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/shop/products/'.$product->slug.'/reviews', $payload)->assertForbidden();
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_paid_customer_review_is_pending_until_approved_and_can_be_updated(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = Order::create([
            'customer_name' => $user->name, 'customer_email' => $user->email,
            'customer_phone' => '08012345678', 'delivery_address' => '1 Test Lane',
            'delivery_state' => 'Bayelsa', 'delivery_method' => 'pickup',
            'subtotal' => 9500, 'total' => 9500, 'payment_gateway' => 'paystack',
            'payment_status' => 'paid', 'order_status' => 'paid',
        ]);
        $order->items()->create([
            'orderable_type' => Product::class, 'orderable_id' => $product->id,
            'item_name' => $product->name, 'unit_price' => 9500, 'quantity' => 1,
            'subtotal' => 9500,
        ]);
        $data = ['rating' => 5, 'display_name' => 'Satisfied Customer',
            'title' => 'Worth the purchase', 'body' => 'Arrived in good condition and works as expected.'];
        $this->actingAs($user)->post('/shop/products/'.$product->slug.'/reviews', $data)->assertRedirect();
        $review = ProductReview::sole();
        $this->assertSame('pending', $review->status);
        $this->get('/gadgets/'.$product->slug)->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Shop/Product')->has('reviews', 0)
                ->where('canReview', true)
        );
        $review->update(['status' => 'approved']);
        $this->get('/gadgets/'.$product->slug)->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Shop/Product')->has('reviews', 1)
                ->where('reviewSummary.count', 1)->where('reviewSummary.average', 5)
        );
        $this->post('/shop/products/'.$product->slug.'/reviews', array_merge($data, ['rating' => 4]))
            ->assertRedirect();
        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertSame('pending', $review->fresh()->status);
    }

    public function test_new_routes_do_not_expose_draft_vehicle_records(): void
    {
        $vehicle = Vehicle::create([
            'title' => 'Private Vehicle', 'make' => 'Toyota', 'model' => 'Corolla',
            'year' => 2018, 'condition' => 'foreign_used', 'price' => 10000000,
            'description' => 'Vehicle available soon', 'is_published' => false,
        ]);
        $this->get('/saved?vehicles='.$vehicle->id)->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Saved/Index')->has('groups.vehicles', 0)
        );
        $this->get('/search?q=Private')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Search/Index')->has('groups.vehicles', 0)
        );
    }
}

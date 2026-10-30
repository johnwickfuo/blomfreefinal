<?php

namespace Tests\Feature;

use App\Models\HomepageBanner;
use App\Models\MarketplacePlacement;
use App\Models\MarketplaceSection;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketplacePhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Bluetooth Headphones', 'subsidiary' => 'gadgets',
            'category' => 'Audio', 'short_description' => 'Wireless listening',
            'description' => 'Bluetooth headphones for everyday listening.',
            'base_price' => 8000, 'compare_price' => 10000,
            'stock' => 5, 'is_published' => true, 'is_featured' => true,
            'has_variants' => false,
        ], $overrides));
    }

    public function test_marketplace_showcases_real_products_with_actual_discounts(): void
    {
        $product = $this->product();
        MarketplacePlacement::create([
            'item_type' => Product::class, 'item_id' => $product->id,
            'section' => 'deals', 'ends_at' => now()->addHour(),
        ]);
        MarketplacePlacement::create([
            'item_type' => Product::class, 'item_id' => $product->id,
            'section' => 'featured',
        ]);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Home')->has('deals', 1)
            ->where('deals.0.price', 8000)
            ->where('deals.0.original_price', 10000)
            ->where('deals.0.discount_percent', 20)
            ->has('featured', 1)
            ->has('byDivision.gadgets', 1)
        );
    }

    public function test_expired_deals_and_unpublished_products_are_not_featured(): void
    {
        $product = $this->product(['is_published' => false]);
        MarketplacePlacement::create([
            'item_type' => Product::class, 'item_id' => $product->id,
            'section' => 'deals', 'ends_at' => now()->subMinute(),
        ]);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Home')->has('deals', 0)->has('featured', 0)->has('byDivision.gadgets', 0)
        );
    }

    public function test_deal_cannot_invent_discounts(): void
    {
        $product = $this->product(['compare_price' => null]);
        MarketplacePlacement::create([
            'item_type' => Product::class, 'item_id' => $product->id, 'section' => 'deals',
        ]);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->has('deals', 0));
    }

    public function test_homepage_section_order_and_visibility_are_admin_controlled(): void
    {
        MarketplaceSection::create(['section_key' => 'featured', 'title' => 'Handpicked Finds',
            'is_enabled' => false, 'display_order' => 3]);
        MarketplaceSection::create(['section_key' => 'new', 'title' => 'Just In',
            'is_enabled' => true, 'display_order' => 1]);
        $data = MarketplaceSection::configured();
        $this->assertSame('new', $data[0]['key']);
        $this->assertSame('featured', $data[1]['key']);
        $this->assertFalse($data[1]['enabled']);
    }

    public function test_category_and_shopping_guides_render(): void
    {
        $this->get('/categories')->assertOk()->assertInertia(fn (Assert $p) =>
            $p->component('Marketplace/Categories'));
        $this->get('/how-it-works')->assertOk();
        $this->get('/payments')->assertOk();
        $this->get('/help')->assertOk();
        $this->get('/my')->assertOk();
    }

    public function test_gadget_price_filter_and_stock_filter(): void
    {
        $this->product(['name' => 'Headset Budget', 'base_price' => 5000, 'compare_price' => null]);
        $this->product(['name' => 'Headset Premium', 'base_price' => 20000, 'compare_price' => null]);
        $this->product(['name' => 'Unavailable', 'base_price' => 7000, 'stock' => 0]);
        $this->get('/gadgets?price_min=4000&price_max=10000')->assertOk()->assertInertia(fn (Assert $page) =>
            $page->component('Shop/Catalog')->has('products.data', 1)
        );
        $this->get('/gadgets?stock_only=0')->assertOk()->assertInertia(fn (Assert $page) =>
            $page->component('Shop/Catalog')->has('products.data', 3)
        );
    }
}

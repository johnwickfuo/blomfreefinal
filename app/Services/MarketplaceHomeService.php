<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Article;
use App\Models\HomepageBanner;
use App\Models\Land;
use App\Models\MarketplacePlacement;
use App\Models\MarketplaceSection;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MarketplaceHomeService
{
    public function data(): array
    {
        $products = Product::query()->where('is_published', true)
            ->whereIn('subsidiary', ['gadgets', 'collections'])
            ->with(['media', 'variants'])->latest()->limit(60)->get()
            ->filter(fn (Product $item) => $item->is_in_stock)->values();
        $vehicles = Vehicle::published()->where('status', 'available')
            ->with('media')->latest()->limit(24)->get();
        $lands = Land::where('status', 'available')->with('media')->latest()->limit(24)->get();
        $animals = Animal::query()->with('media')
            ->where(fn ($q) => $q->where(fn ($p) => $p->where('listing_type', 'pool')->where('stock', '>', 0))
                ->orWhere(fn ($one) => $one->where('listing_type', 'individual')->where('availability', 'available')))
            ->latest()->limit(24)->get();

        $all = collect()
            ->concat($products)->concat($vehicles)->concat($lands)->concat($animals);
        $placements = MarketplacePlacement::query()->current()->with('item')
            ->orderBy('display_order')->orderByDesc('created_at')->limit(90)->get();
        $placements->loadMorph('item', [
            Product::class => ['media', 'variants'],
            Vehicle::class => ['media'],
            Land::class => ['media'],
            Animal::class => ['media'],
        ]);
        $pinned = fn (string $section) => $placements->where('section', $section)
            ->filter(fn (MarketplacePlacement $p) => $p->item && $this->isAvailable($p->item))
            ->map(function (MarketplacePlacement $p) use ($section) {
                $card = $this->card($p->item);
                if ($section === 'deals') {
                    if (! $p->item instanceof Product || ! $this->hasDiscount($p->item)) return null;
                    $card['ends_at'] = $p->ends_at?->toIso8601String();
                }
                return $card;
            })->filter()->values()->take(10)->all();

        $featured = $pinned('featured');
        $seen = collect($featured)->pluck('type', 'key');
        if (count($featured) < 8) {
            $fallback = $all->filter(fn ($item) => (bool) $item->is_featured && $this->isAvailable($item))
                ->map(fn ($item) => $this->card($item))->filter(fn ($card) => ! $seen->has($card['key']))
                ->take(8 - count($featured))->values()->all();
            $featured = array_merge($featured, $fallback);
        }

        $byDivision = [
            'gadgets' => $products->where('subsidiary', 'gadgets')->take(8)->map(fn ($p) => $this->card($p))->values()->all(),
            'collections' => $products->where('subsidiary', 'collections')->take(8)->map(fn ($p) => $this->card($p))->values()->all(),
            'vehicles' => $vehicles->take(8)->map(fn ($p) => $this->card($p))->all(),
            'lands' => $lands->take(8)->map(fn ($p) => $this->card($p))->all(),
            'animals' => $animals->take(8)->map(fn ($p) => $this->card($p))->all(),
        ];

        $newArrivals = $all->sortByDesc('created_at')->take(10)
            ->map(fn ($p) => $this->card($p))->values()->all();

        $banners = HomepageBanner::query()->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->orderBy('display_order')->limit(6)->get()
            ->map(fn ($banner) => [
                'id' => $banner->id, 'eyebrow' => $banner->eyebrow,
                'headline' => $banner->headline, 'description' => $banner->description,
                'image' => $banner->image_path ? Storage::disk('public')->url($banner->image_path) : null,
                'button_label' => $banner->button_label,
                'button_url' => preg_match('~^/(?!/)[a-zA-Z0-9/_?=&%-]*$~', $banner->button_url ?? '') ? $banner->button_url : null,
            ])->all();

        $articles = Article::query()->where('is_published', true)
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->latest('published_at')->limit(3)->get()
            ->map(fn (Article $article) => [
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'href' => route('articles.show', $article->slug),
                'image' => $article->image_path ? Storage::disk('public')->url($article->image_path) : null,
            ])->all();

        $testimonials = Testimonial::where('is_approved', true)->orderBy('display_order')
            ->limit(3)->get()->map(fn (Testimonial $item) => [
                'id' => $item->id, 'name' => $item->customer_name, 'quote' => $item->quote,
                'division' => $item->division,
            ])->all();

        return [
            'testimonials' => $testimonials,
            'featured' => $featured, 'deals' => $pinned('deals'), 'spotlight' => $pinned('spotlight'),
            'byDivision' => $byDivision, 'newArrivals' => $newArrivals,
            'banners' => $banners, 'articles' => $articles,
            'sections' => MarketplaceSection::configured(),
        ];
    }

    public function isAvailable(object $item): bool
    {
        return match (true) {
            $item instanceof Product => $item->is_published && $item->is_in_stock,
            $item instanceof Vehicle => $item->is_published && $item->status === 'available',
            $item instanceof Land => $item->status === 'available',
            $item instanceof Animal => $item->listing_type === 'pool'
                ? (int) $item->stock > 0 : $item->availability === 'available',
            default => false,
        };
    }

    public function hasDiscount(Product $p): bool
    {
        return $p->compare_price !== null && (float) $p->compare_price > (float) $p->display_price;
    }

    public function card(object $item): array
    {
        $type = match (true) {
            $item instanceof Product => $item->subsidiary,
            $item instanceof Vehicle => 'vehicles',
            $item instanceof Land => 'lands',
            $item instanceof Animal => 'animals',
        };
        $price = match (true) {
            $item instanceof Product => (float) $item->display_price,
            $item instanceof Vehicle => (float) $item->price,
            $item instanceof Land => (float) $item->price_per_plot,
            $item instanceof Animal => (float) $item->price,
        };
        $originalPrice = $item instanceof Product && $this->hasDiscount($item)
            ? (float) $item->compare_price : null;
        $href = match (true) {
            $item instanceof Product => route($type.'.show', $item),
            $item instanceof Vehicle => route('automobiles.show', $item),
            $item instanceof Land => route('lands.show', $item),
            $item instanceof Animal => route('kennel-farm.show', $item),
        };
        return [
            'key' => $type.'-'.$item->id,
            'id' => $item->id, 'type' => $type,
            'title' => $item instanceof Product || $item instanceof Animal ? $item->name : $item->title,
            'subtitle' => match (true) {
                $item instanceof Product => $item->category,
                $item instanceof Vehicle => $item->year.' · '.$item->make.' · '.$item->model,
                $item instanceof Land => $item->city_or_lga.', '.$item->state,
                $item instanceof Animal => $item->breed,
            },
            'image' => $item->cover_url,
            'price' => $price, 'original_price' => $originalPrice,
            'discount_percent' => $originalPrice ? (int) round((1 - $price / $originalPrice) * 100) : null,
            'stock' => $item instanceof Product && ! $item->has_variants ? $item->stock : null,
            'action' => $item instanceof Product ? ($item->has_variants ? 'Choose options' : 'Add to cart')
                : ($item instanceof Land ? 'Book inspection' : ($item instanceof Vehicle ? 'Enquire' : 'View details')),
            'can_add' => $item instanceof Product && ! $item->has_variants && $item->is_in_stock,
            'href' => $href,
        ];
    }
}

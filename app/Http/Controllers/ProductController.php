<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function collectionsIndex(Request $request): Response
    {
        return $this->index($request, 'collections');
    }

    public function gadgetsIndex(Request $request): Response
    {
        return $this->index($request, 'gadgets');
    }

    public function collectionsShow(Product $product): Response
    {
        return $this->show($product, 'collections');
    }

    public function gadgetsShow(Product $product): Response
    {
        return $this->show($product, 'gadgets');
    }

    private function index(Request $request, string $subsidiary): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:featured,newest,price_asc,price_desc'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'stock_only' => ['nullable', 'boolean'],
        ]);
        $category = $filters['category'] ?? null;
        $sort = $filters['sort'] ?? 'featured';
        $stockOnly = ! isset($filters['stock_only']) || (bool) $filters['stock_only'];

        $query = Product::query()->with(['variants', 'media'])
            ->where('subsidiary', $subsidiary)->where('is_published', true);

        if ($category) $query->where('category', $category);
        if ($stockOnly) {
            $query->where(fn ($q) => $q->where(fn ($simple) => $simple->where('has_variants', false)->where('stock', '>', 0))
                ->orWhere(fn ($variant) => $variant->where('has_variants', true)
                    ->whereHas('variants', fn ($v) => $v->where('stock', '>', 0))));
        }

        // Match Product::display_price even when every variant has a common override.
        $effectivePrice = "CASE WHEN products.has_variants = 1
            AND EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = products.id)
            AND NOT EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = products.id AND v.price_override IS NULL)
            AND (SELECT COUNT(DISTINCT v.price_override) FROM product_variants v WHERE v.product_id = products.id) = 1
            THEN (SELECT MIN(v.price_override) FROM product_variants v WHERE v.product_id = products.id)
            ELSE products.base_price END";
        foreach (['price_min' => '>=', 'price_max' => '<='] as $key => $operator) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $query->whereRaw("($effectivePrice) $operator CAST(? AS DECIMAL(16,2))", [(float) $filters[$key]]);
            }
        }
        match ($sort) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderByRaw("($effectivePrice) ASC"),
            'price_desc' => $query->orderByRaw("($effectivePrice) DESC"),
            default => $query->orderByDesc('is_featured')->orderBy('order'),
        };
        $products = $query->orderByDesc('id')->paginate(12)->withQueryString()
            ->through(fn (Product $product): array => $this->cardData($product));

        $categories = Product::query()->where('subsidiary', $subsidiary)
            ->where('is_published', true)->distinct()->orderBy('category')->pluck('category');

        return Inertia::render('Shop/Catalog', [
            'subsidiary' => $subsidiary, 'products' => $products, 'categories' => $categories,
            'filters' => [
                'category' => $category,
                'sort' => $sort,
                'price_min' => $filters['price_min'] ?? null,
                'price_max' => $filters['price_max'] ?? null,
                'stock_only' => $stockOnly,
            ],
        ]);
    }

    private function show(Product $product, string $subsidiary): Response
    {
        abort_unless($product->subsidiary === $subsidiary && $product->is_published, 404);

        $product->load(['variants', 'media']);

        $attributeKeys = $product->attribute_keys ?? [];

        // Pre-computed variant data so the page can enable/disable options.
        $variants = $product->variants
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'attributes' => $variant->attributes,
                'label' => $variant->attributes_label,
                'sku' => $variant->sku,
                'price' => $variant->effectivePrice(),
                'stock' => $variant->stock,
                'in_stock' => $variant->stock > 0,
            ])
            ->values();

        // Distinct option values per attribute key, in first-seen order.
        $optionValues = [];
        foreach ($attributeKeys as $key) {
            $optionValues[$key] = $product->variants
                ->map(fn (ProductVariant $variant) => $variant->attributes[$key] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $related = Product::query()
            ->with(['variants', 'media'])
            ->where('subsidiary', $subsidiary)
            ->where('is_published', true)
            ->where('category', $product->category)
            ->whereKeyNot($product->getKey())
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->limit(4)
            ->get()
            ->map(fn (Product $related): array => $this->cardData($related))
            ->values();

        return Inertia::render('Shop/Product', [
            'subsidiary' => $subsidiary,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => $product->category,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'base_price' => $product->base_price,
                'compare_price' => $product->compare_price,
                'display_price' => $product->display_price,
                'has_variants' => $product->has_variants,
                'stock' => $product->stock,
                'is_in_stock' => $product->is_in_stock,
                'attribute_keys' => $attributeKeys,
                'gallery_urls' => $product->galleryUrls(),
                'cover_url' => $product->cover_url,
            ],
            'variants' => $variants,
            'optionValues' => $optionValues,
            'related' => $related,
            'installment' => $this->installmentSummary($product, $subsidiary),
            'reviews' => ProductReview::query()->approved()->where('product_id', $product->id)
                ->latest()->limit(20)->get(['id', 'rating', 'display_name', 'title', 'body', 'created_at'])
                ->map(fn (ProductReview $review): array => [
                    'id' => $review->id, 'rating' => $review->rating, 'name' => $review->display_name,
                    'title' => $review->title, 'body' => $review->body,
                    'date' => $review->created_at->toDateString(),
                ]),
            'reviewSummary' => [
                'count' => ProductReview::query()->approved()->where('product_id', $product->id)->count(),
                'average' => round((float) ProductReview::query()->approved()->where('product_id', $product->id)->avg('rating'), 1),
            ],
            'canReview' => auth()->check() && ProductReview::hasVerifiedPurchase(auth()->user(), $product),
            'myReview' => auth()->check()
                ? ProductReview::query()->where('product_id', $product->id)->where('user_id', auth()->id())->first(['rating', 'display_name', 'title', 'body', 'status'])
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function installmentSummary(Product $product, string $subsidiary): ?array
    {
        if ($subsidiary !== 'gadgets') {
            return null;
        }
        if (! $product->installment_enabled) {
            return null;
        }
        if ($product->has_variants) {
            // For variant products, surface the parent-level config — the
            // initiate page resolves per-variant details once the customer
            // picks one.
            return [
                'enabled' => true,
                'down_payment_percentage' => $product->installment_minimum_down_payment_percentage,
                'maximum_length_months' => $product->installment_maximum_length_months,
                'requires_variant_selection' => true,
                'initiate_url' => '/installments/initiate?type=product&id='.$product->id,
            ];
        }

        return [
            'enabled' => true,
            'down_payment_percentage' => $product->installment_minimum_down_payment_percentage,
            'maximum_length_months' => $product->installment_maximum_length_months,
            'requires_variant_selection' => false,
            'initiate_url' => '/installments/initiate?type=product&id='.$product->id,
        ];
    }

    /**
     * Shape a product for a catalog / related-products card.
     *
     * @return array<string, mixed>
     */
    private function cardData(Product $product): array
    {
        $colors = [];

        if ($product->has_variants && in_array('color', $product->attribute_keys ?? [], true)) {
            $colors = $product->variants
                ->map(fn (ProductVariant $variant) => $variant->attributes['color'] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [
            'id' => $product->id,
            'has_variants' => $product->has_variants,
            'name' => $product->name,
            'slug' => $product->slug,
            'category' => $product->category,
            'cover_url' => $product->cover_url,
            'display_price' => $product->display_price,
            'compare_price' => $product->compare_price,
            'is_in_stock' => $product->is_in_stock,
            'colors' => $colors,
        ];
    }
}

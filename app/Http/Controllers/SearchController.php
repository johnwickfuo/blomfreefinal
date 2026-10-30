<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Article;
use App\Models\Land;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', 'in:all,properties,animals,collections,gadgets,vehicles,articles'],
        ]);
        $term = trim($data['q'] ?? '');
        $type = $data['type'] ?? 'all';
        $groups = [
            'properties' => [], 'animals' => [], 'collections' => [],
            'gadgets' => [], 'vehicles' => [], 'articles' => [],
        ];

        if (mb_strlen($term) < 2) {
            return Inertia::render('Search/Index', [
                'query' => $term, 'activeType' => $type, 'groups' => $groups,
                'hasSearched' => false,
            ]);
        }

        $like = '%'.$term.'%';
        $include = fn (string $name): bool => $type === 'all' || $type === $name;

        if ($include('properties')) {
            $groups['properties'] = Land::query()->where('status', 'available')
                ->where(fn (Builder $q) => $q->where('title', 'like', $like)
                    ->orWhere('state', 'like', $like)->orWhere('city_or_lga', 'like', $like))
                ->with('media')->orderByDesc('is_featured')->limit(8)->get()
                ->map(fn (Land $land): array => [
                    'id' => $land->id, 'title' => $land->title, 'subtitle' => $land->city_or_lga.', '.$land->state,
                    'image' => $land->cover_url, 'price' => (float) $land->price_per_plot,
                    'href' => route('lands.show', $land),
                ])->all();
        }

        if ($include('animals')) {
            $groups['animals'] = Animal::query()
                ->where(fn (Builder $q) => $q->where(fn (Builder $pool) => $pool->where('listing_type', 'pool')->where('stock', '>', 0))
                    ->orWhere(fn (Builder $one) => $one->where('listing_type', 'individual')->where('availability', 'available')))
                ->where(fn (Builder $q) => $q->where('name', 'like', $like)
                    ->orWhere('breed', 'like', $like)->orWhere('category', 'like', $like))
                ->with('media')->limit(8)->get()->map(fn (Animal $animal): array => [
                    'id' => $animal->id, 'title' => $animal->name, 'subtitle' => $animal->breed,
                    'image' => $animal->cover_url, 'price' => (float) $animal->price,
                    'href' => route('kennel-farm.show', $animal),
                ])->all();
        }

        foreach (['collections', 'gadgets'] as $division) {
            if (! $include($division)) continue;
            $groups[$division] = Product::query()->where('is_published', true)->where('subsidiary', $division)
                ->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('category', 'like', $like)
                    ->orWhere('short_description', 'like', $like))
                ->with(['media', 'variants'])->limit(40)->get()
                ->filter(fn (Product $p): bool => $p->is_in_stock)->take(8)
                ->map(fn (Product $p): array => [
                    'id' => $p->id, 'title' => $p->name, 'subtitle' => $p->category,
                    'image' => $p->cover_url, 'price' => $p->display_price,
                    'href' => route($division.'.show', $p),
                ])->values()->all();
        }

        if ($include('vehicles')) {
            $groups['vehicles'] = Vehicle::published()->where('status', 'available')
                ->where(fn (Builder $q) => $q->where('title', 'like', $like)
                    ->orWhere('make', 'like', $like)->orWhere('model', 'like', $like))
                ->with('media')->limit(8)->get()->map(fn (Vehicle $v): array => [
                    'id' => $v->id, 'title' => $v->title, 'subtitle' => $v->year.' · '.$v->make.' · '.$v->model,
                    'image' => $v->cover_url, 'price' => (float) $v->price,
                    'href' => route('automobiles.show', $v),
                ])->all();
        }

        if ($include('articles')) {
            $groups['articles'] = Article::query()->where('is_published', true)->where('published_at', '<=', now())
                ->whereNotNull('published_at')
                ->where(fn (Builder $q) => $q->where('title', 'like', $like)->orWhere('excerpt', 'like', $like))
                ->latest('published_at')->limit(8)->get()
                ->map(fn (Article $article): array => [
                    'id' => $article->id, 'title' => $article->title, 'subtitle' => $article->category,
                    'image' => $article->image_path
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) : null,
                    'price' => null, 'href' => route('articles.show', $article->slug),
                ])->all();
        }

        return Inertia::render('Search/Index', [
            'query' => $term, 'activeType' => $type, 'groups' => $groups, 'hasSearched' => true,
        ]);
    }
}

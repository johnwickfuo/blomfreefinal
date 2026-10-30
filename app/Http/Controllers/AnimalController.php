<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnimalController extends Controller
{
    private const TAB_CATEGORIES = ['dog', 'cat', 'rabbit', 'grasscutter'];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'in:dog,cat,rabbit,grasscutter'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
        ]);
        $query = Animal::query()
            ->with('media')
            ->where(fn (Builder $query) => $query
                ->where('listing_type', 'pool')
                ->orWhere('availability', '!=', 'sold'))
            ->when(isset($filters['price_min']) && $filters['price_min'] !== '', fn (Builder $q) => $q->where('price', '>=', (float) $filters['price_min']))
            ->when(isset($filters['price_max']) && $filters['price_max'] !== '', fn (Builder $q) => $q->where('price', '<=', (float) $filters['price_max']))
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        $animalsByCategory = [];
        foreach (self::TAB_CATEGORIES as $category) {
            $animalsByCategory[$category] = ($animals->get($category) ?? collect())->values();
        }

        return Inertia::render('KennelFarm/Index', [
            'animalsByCategory' => $animalsByCategory,
            'filters' => $filters,
        ]);
    }

    public function show(Animal $animal): Response
    {
        $animal->load('media')->append('gallery_urls');

        $related = Animal::query()
            ->with('media')
            ->where('category', $animal->category)
            ->whereKeyNot($animal->getKey())
            ->where(fn (Builder $query) => $query
                ->where('listing_type', 'pool')
                ->orWhere('availability', '!=', 'sold'))
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->limit(3)
            ->get();

        return Inertia::render('KennelFarm/Show', [
            'animal' => $animal,
            'related' => $related,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Land;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedListingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'lands' => ['nullable', 'string', 'max:350'],
            'animals' => ['nullable', 'string', 'max:350'],
            'products' => ['nullable', 'string', 'max:350'],
            'vehicles' => ['nullable', 'string', 'max:350'],
        ]);
        $ids = [];
        foreach (['lands', 'animals', 'products', 'vehicles'] as $type) {
            $raw = $data[$type] ?? '';
            abort_unless($raw === '' || preg_match('/^[0-9]+(,[0-9]+)*$/', $raw), 422);
            $ids[$type] = collect(explode(',', $raw))->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)->unique()->take(30)->values()->all();
        }

        $items = [
            'lands' => Land::whereIn('id', $ids['lands'])->where('status', 'available')->with('media')->get()
                ->map(fn (Land $v) => ['id' => $v->id, 'title' => $v->title, 'price' => (float) $v->price_per_plot,
                    'image' => $v->cover_url, 'meta' => $v->city_or_lga.', '.$v->state, 'href' => route('lands.show', $v)])->all(),
            'animals' => Animal::whereIn('id', $ids['animals'])->with('media')
                ->where(fn ($q) => $q->where(fn ($p) => $p->where('listing_type', 'pool')->where('stock', '>', 0))
                    ->orWhere(fn ($p) => $p->where('listing_type', 'individual')->where('availability', 'available')))
                ->get()->map(fn (Animal $v) => ['id' => $v->id, 'title' => $v->name, 'price' => (float) $v->price,
                    'image' => $v->cover_url, 'meta' => $v->breed, 'href' => route('kennel-farm.show', $v)])->all(),
            'products' => Product::whereIn('id', $ids['products'])->where('is_published', true)
                ->with(['media','variants'])->get()->filter(fn (Product $p) => $p->is_in_stock)
                ->map(fn (Product $v) => ['id' => $v->id, 'title' => $v->name, 'price' => $v->display_price,
                    'image' => $v->cover_url, 'meta' => ucfirst($v->subsidiary), 'href' => route($v->subsidiary.'.show', $v)])->values()->all(),
            'vehicles' => Vehicle::published()->whereIn('id', $ids['vehicles'])->where('status', 'available')->with('media')->get()
                ->map(fn (Vehicle $v) => ['id' => $v->id, 'title' => $v->title, 'price' => (float) $v->price,
                    'image' => $v->cover_url, 'meta' => $v->year.' · '.$v->make, 'href' => route('automobiles.show', $v)])->all(),
        ];

        return Inertia::render('Saved/Index', ['groups' => $items]);
    }
}

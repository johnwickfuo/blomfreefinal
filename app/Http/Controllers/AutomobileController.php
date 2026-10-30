<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AutomobileController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'condition' => ['nullable', Rule::in(array_keys(Vehicle::CONDITIONS))],
            'body_type' => ['nullable', Rule::in(array_keys(Vehicle::BODY_TYPES))],
            'transmission' => ['nullable', Rule::in(array_keys(Vehicle::TRANSMISSIONS))],
            'fuel_type' => ['nullable', Rule::in(array_keys(Vehicle::FUELS))],
            'state' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Vehicle::STATUSES))],
            'year_min' => ['nullable', 'integer', 'between:1950,2100'],
            'year_max' => ['nullable', 'integer', 'between:1950,2100'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'mileage_max' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'year_desc', 'year_asc', 'mileage_asc'])],
        ]);

        $query = Vehicle::published()->with('media');

        foreach (['make', 'model', 'condition', 'body_type', 'transmission', 'fuel_type', 'state'] as $key) {
            if (filled($filters[$key] ?? null)) {
                $query->where($key, $filters[$key]);
            }
        }

        $query->where('status', $filters['status'] ?? 'available');

        if (filled($filters['q'] ?? null)) {
            $term = $filters['q'];
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', '%'.$term.'%')
                    ->orWhere('make', 'like', '%'.$term.'%')
                    ->orWhere('model', 'like', '%'.$term.'%');
            });
        }
        foreach (['year_min' => ['year', '>='], 'year_max' => ['year', '<='],
            'price_min' => ['price', '>='], 'price_max' => ['price', '<='],
            'mileage_max' => ['mileage_km', '<=']] as $key => [$column, $operator]) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $query->where($column, $operator, $filters[$key]);
            }
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'year_desc' => $query->orderByDesc('year'),
            'year_asc' => $query->orderBy('year'),
            'mileage_asc' => $query->orderBy('mileage_km'),
            default => $query->latest(),
        };
        $query->orderByDesc('id');

        return Inertia::render('Automobiles/Index', [
            'vehicles' => $query->paginate(12)->withQueryString()->through(fn (Vehicle $v): array => $v->card()),
            'filters' => $filters,
            'makes' => Vehicle::published()->distinct()->orderBy('make')->pluck('make'),
            'models' => Vehicle::published()->when($filters['make'] ?? null, fn ($q, $make) => $q->where('make', $make))
                ->distinct()->orderBy('model')->pluck('model'),
            'states' => Vehicle::published()->whereNotNull('state')->distinct()->orderBy('state')->pluck('state'),
            'conditions' => Vehicle::CONDITIONS,
            'bodyTypes' => Vehicle::BODY_TYPES,
            'transmissions' => Vehicle::TRANSMISSIONS,
            'fuels' => Vehicle::FUELS,
        ]);
    }

    public function show(Vehicle $vehicle): Response
    {
        abort_unless($vehicle->is_published, 404);
        $vehicle->load('media');
        $related = Vehicle::published()->where('status', 'available')
            ->whereKeyNot($vehicle->getKey())
            ->where(fn ($q) => $q->where('make', $vehicle->make)->orWhere('body_type', $vehicle->body_type))
            ->with('media')->latest()->limit(3)->get();

        return Inertia::render('Automobiles/Show', [
            'vehicle' => $vehicle->publicDetails(),
            'related' => $related->map(fn (Vehicle $v): array => $v->card()),
        ]);
    }

    public function compare(Request $request): Response
    {
        $ids = $this->ids($request);
        return Inertia::render('Automobiles/Compare', [
            'vehicles' => Vehicle::published()->with('media')->whereIn('id', $ids)
                ->get()->sortBy(fn ($v) => array_search($v->id, $ids, true))->values()
                ->map(fn (Vehicle $v): array => $v->publicDetails()),
        ]);
    }

    public function saved(Request $request): Response
    {
        $ids = $this->ids($request, 30);
        return Inertia::render('Automobiles/Saved', [
            'vehicles' => Vehicle::published()->with('media')->whereIn('id', $ids)
                ->get()->map(fn (Vehicle $v): array => $v->card()),
        ]);
    }

    private function ids(Request $request, int $limit = 3): array
    {
        $raw = (string) $request->query('ids', '');
        abort_if(strlen($raw) > 400, 422);
        if ($raw === '') {
            return [];
        }
        abort_unless(preg_match('/^[0-9]+(,[0-9]+)*$/', $raw), 422);
        return collect(explode(',', $raw))->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)->unique()->take($limit)->values()->all();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Models\Product;
use App\Models\Vehicle;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceCategoryController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Marketplace/Categories', [
            'productCategories' => [
                'gadgets' => Product::where('is_published', true)->where('subsidiary', 'gadgets')
                    ->distinct()->orderBy('category')->pluck('category')->all(),
                'collections' => Product::where('is_published', true)->where('subsidiary', 'collections')
                    ->distinct()->orderBy('category')->pluck('category')->all(),
            ],
            'vehicleMakes' => Vehicle::published()->where('status', 'available')->distinct()->orderBy('make')->pluck('make')->all(),
            'propertyStates' => Land::where('status', 'available')->whereNotNull('state')
                ->distinct()->orderBy('state')->pluck('state')->all(),
        ]);
    }
}

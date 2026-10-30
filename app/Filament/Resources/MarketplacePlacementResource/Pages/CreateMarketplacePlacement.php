<?php

namespace App\Filament\Resources\MarketplacePlacementResource\Pages;

use App\Filament\Resources\MarketplacePlacementResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateMarketplacePlacement extends CreateRecord
{
    protected static string $resource = MarketplacePlacementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['section'] === 'deals') $data['item_type'] = Product::class;
        if ($data['section'] === 'deals') {
            $p = Product::with('variants')->find($data['item_id']);
            if (! $p || ! $p->is_published || ! $p->is_in_stock ||
                $p->compare_price === null || (float) $p->compare_price <= (float) $p->display_price) {
                throw ValidationException::withMessages(['data.item_id' => 'Deals need an in-stock, published product whose regular comparison price exceeds its actual selling price.']);
            }
        }
        return $data;
    }
}

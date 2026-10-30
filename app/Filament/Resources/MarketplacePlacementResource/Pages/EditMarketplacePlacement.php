<?php

namespace App\Filament\Resources\MarketplacePlacementResource\Pages;

use App\Filament\Resources\MarketplacePlacementResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMarketplacePlacement extends EditRecord
{
    protected static string $resource = MarketplacePlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['section'] === 'deals') $data['item_type'] = Product::class;
        if ($data['section'] === 'deals') {
            $p = Product::with('variants')->find($data['item_id']);
            if (! $p || ! $p->is_published || ! $p->is_in_stock ||
                $p->compare_price === null || (float) $p->compare_price <= (float) $p->display_price) {
                throw ValidationException::withMessages(['data.item_id' => 'Select an in-stock product with a genuine comparison-price discount.']);
            }
        }
        return $data;
    }
}

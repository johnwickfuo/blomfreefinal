<?php
namespace App\Filament\Resources\MarketplaceSectionResource\Pages;
use App\Filament\Resources\MarketplaceSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListMarketplaceSections extends ListRecords
{
    protected static string $resource = MarketplaceSectionResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

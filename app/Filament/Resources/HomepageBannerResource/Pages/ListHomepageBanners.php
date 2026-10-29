<?php

namespace App\Filament\Resources\HomepageBannerResource\Pages;

use App\Filament\Resources\HomepageBannerResource;
use Filament\Resources\Pages\ListRecords;

class ListHomepageBanners extends ListRecords
{
    protected static string $resource = HomepageBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}

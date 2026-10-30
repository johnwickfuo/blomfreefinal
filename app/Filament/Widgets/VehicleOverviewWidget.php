<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use App\Models\Inspection;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleOverviewWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('BLOMWHEELS listed', Vehicle::query()->count()),
            Stat::make('Available vehicles', Vehicle::query()->where('status', 'available')->where('is_published', true)->count()),
            Stat::make('New vehicle inquiries', Inquiry::query()->whereNotNull('vehicle_id')->where('status', 'new')->count()),
            Stat::make('Pending vehicle inspections', Inspection::query()
                ->where('inspectable_type', Vehicle::class)->where('status', 'pending')->count()),
        ];
    }
}

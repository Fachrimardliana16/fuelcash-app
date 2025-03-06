<?php

namespace App\Filament\Resources\VehicleResource\Widgets;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Kendaraan', Vehicle::count())
                ->description('Semua jenis kendaraan')
                ->icon('heroicon-o-calculator')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->color('info'),

            Stat::make('Motor', Vehicle::whereHas('vehicleType', function ($query) {
                $query->where('name', 'like', '%roda dua%');
            })->count())
                ->description('Kendaraan Roda Dua')
                ->icon('heroicon-o-bolt')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([3, 2, 6, 4, 5, 3, 6])
                ->color('success'),

            Stat::make('Mobil', Vehicle::whereHas('vehicleType', function ($query) {
                $query->where('name', 'like', '%roda empat%');
            })->count())
                ->description('Kendaraan Roda Empat')
                ->icon('heroicon-o-truck')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([4, 5, 3, 6, 3, 5, 4])
                ->color('warning'),
        ];
    }
}

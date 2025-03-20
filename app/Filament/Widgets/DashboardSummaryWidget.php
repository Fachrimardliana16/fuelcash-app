<?php

namespace App\Filament\Widgets;

use App\Models\Fuel;
use App\Models\Transaction;
use App\Models\VehicleType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class DashboardSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Get common metrics needed for analysis
        $todayCount = Transaction::whereDate('usage_date', Carbon::today())->count();
        $yesterdayCount = Transaction::whereDate('usage_date', Carbon::yesterday())->count();

        // Get top fuel type by transaction count
        $topFuel = Transaction::join('fuels', 'transactions.fuel_id', '=', 'fuels.id')
            ->whereMonth('usage_date', Carbon::now()->month)
            ->select('fuels.name', DB::raw('COUNT(*) as count'))
            ->groupBy('fuels.name')
            ->orderByDesc('count')
            ->first();

        // Get top vehicle type
        $topVehicle = Transaction::join('vehicle_types', 'transactions.vehicle_type_id', '=', 'vehicle_types.id')
            ->whereMonth('usage_date', Carbon::now()->month)
            ->select('vehicle_types.name', DB::raw('COUNT(*) as count'))
            ->groupBy('vehicle_types.name')
            ->orderByDesc('count')
            ->first();

        // Calculate weekly trend
        $thisWeekCount = Transaction::whereBetween('usage_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()
        ])->count();

        $lastWeekCount = Transaction::whereBetween('usage_date', [
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek()
        ])->count();

        $weekTrend = $lastWeekCount > 0
            ? (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100
            : 0;

        return [
            Stat::make('Jumlah Transaksi Hari Ini', (string)$todayCount)
                ->description(new HtmlString("vs kemarin: {$yesterdayCount} transaksi"))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('BBM Terbanyak', $topFuel ? $topFuel->name : '-')
                ->description($topFuel ? "{$topFuel->count} transaksi bulan ini" : "Belum ada data")
                ->descriptionIcon('heroicon-m-fire')
                ->color('success'),

            Stat::make('Kendaraan Terbanyak', $topVehicle ? $topVehicle->name : '-')
                ->description($topVehicle ? "{$topVehicle->count} transaksi bulan ini" : "Belum ada data")
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Tren Mingguan', number_format(abs($weekTrend), 0) . '% ' . ($weekTrend >= 0 ? '↑' : '↓'))
                ->description(new HtmlString("Minggu ini: {$thisWeekCount} transaksi"))
                ->descriptionIcon($weekTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weekTrend > 10 ? 'danger' : ($weekTrend < 0 ? 'success' : 'info')),
        ];
    }
}

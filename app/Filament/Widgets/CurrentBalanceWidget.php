<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\FuelType;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CurrentBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Get all active fuel types
        $fuelTypes = FuelType::where('isactive', true)->get();

        $stats = [];

        // Add overall balance stat
        $totalBalance = 0;
        foreach ($fuelTypes as $fuelType) {
            $latestBalance = Balance::where('fuel_type_id', $fuelType->id)
                ->latest()
                ->first();

            $totalBalance += $latestBalance ? $latestBalance->remaining_balance : 0;
        }

        $todayTransactions = Transaction::whereDate('usage_date', Carbon::today())->sum('amount');
        $yesterdayTransactions = Transaction::whereDate('usage_date', Carbon::yesterday())->sum('amount');

        $stats[] = Stat::make('Total Saldo Saat Ini', 'Rp ' . number_format($totalBalance, 0, ',', '.'))
            ->description('Seluruh saldo BBM yang tersedia')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success');

        $stats[] = Stat::make('Transaksi Hari Ini', 'Rp ' . number_format($todayTransactions, 0, ',', '.'))
            ->description('Total pengeluaran hari ini')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger');

        $stats[] = Stat::make('Transaksi Kemarin', 'Rp ' . number_format($yesterdayTransactions, 0, ',', '.'))
            ->description('Total pengeluaran kemarin')
            ->descriptionIcon('heroicon-m-calendar')
            ->color('warning');

        $thisMonthSpending = Transaction::whereMonth('usage_date', Carbon::now()->month)
            ->whereYear('usage_date', Carbon::now()->year)
            ->sum('amount');

        $stats[] = Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($thisMonthSpending, 0, ',', '.'))
            ->description('Total bulan ' . Carbon::now()->format('F Y'))
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color('info');

        return $stats;
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\FuelType;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class CurrentBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Calculate total balance
        $totalBalance = Balance::whereIn(
            'fuel_type_id',
            FuelType::where('isactive', true)->pluck('id')
        )
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('balances')
                    ->groupBy('fuel_type_id');
            })
            ->sum('remaining_balance');

        // Get key transaction metrics
        $todayTransactions = Transaction::whereDate('usage_date', Carbon::today())->sum('amount');
        $yesterdayTransactions = Transaction::whereDate('usage_date', Carbon::yesterday())->sum('amount');
        $thisMonthSpending = Transaction::whereMonth('usage_date', Carbon::now()->month)
            ->whereYear('usage_date', Carbon::now()->year)
            ->sum('amount');
        $lastMonthTotal = Transaction::whereMonth('usage_date', Carbon::now()->subMonth()->month)
            ->whereYear('usage_date', Carbon::now()->subMonth()->year)
            ->sum('amount');

        // Calculate month progress percentage
        $dayOfMonth = Carbon::now()->day;
        $daysInMonth = Carbon::now()->daysInMonth;
        $monthProgress = ($dayOfMonth / $daysInMonth) * 100;

        // Create simplified, compact stats
        return [
            Stat::make('Total Saldo', 'Rp ' . number_format($totalBalance, 0, ',', '.'))
                ->description('Jumlah seluruh saldo BBM')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Hari Ini', 'Rp ' . number_format($todayTransactions, 0, ',', '.'))
                ->description('vs kemarin: Rp ' . number_format($yesterdayTransactions, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Bulan Ini', 'Rp ' . number_format($thisMonthSpending, 0, ',', '.'))
                ->description(number_format($monthProgress, 0) . '% dari bulan berjalan')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            Stat::make('vs Bulan Lalu', number_format(($lastMonthTotal > 0 ? $thisMonthSpending / $lastMonthTotal * 100 : 0), 0) . '%')
                ->description('Bulan lalu: Rp ' . number_format($lastMonthTotal, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($thisMonthSpending > $lastMonthTotal ? 'danger' : 'success'),
        ];
    }
}

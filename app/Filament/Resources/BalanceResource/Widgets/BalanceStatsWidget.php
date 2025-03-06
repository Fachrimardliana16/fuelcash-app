<?php

namespace App\Filament\Resources\BalanceResource\Widgets;

use App\Models\Balance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class BalanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $lastMonth = $now->copy()->subMonth();
        $lastYear = $now->copy()->subYear();

        // Calculate statistics
        $currentMonthDeposit = Balance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('deposit_amount');

        $lastMonthDeposit = Balance::whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year)
            ->sum('deposit_amount');

        $currentYearDeposit = Balance::whereYear('date', $currentYear)
            ->sum('deposit_amount');

        $lastYearDeposit = Balance::whereYear('date', $lastYear->year)
            ->sum('deposit_amount');

        // Calculate trends
        $monthlyTrend = $lastMonthDeposit != 0
            ? (($currentMonthDeposit - $lastMonthDeposit) / $lastMonthDeposit * 100)
            : 0;

        $yearlyTrend = $lastYearDeposit != 0
            ? (($currentYearDeposit - $lastYearDeposit) / $lastYearDeposit * 100)
            : 0;

        $lastBalance = Balance::latest()->first()?->remaining_balance ?? 0;

        return [
            Stat::make('Monthly Deposits', 'Rp ' . number_format($currentMonthDeposit, 0, ',', '.'))
                ->description($monthlyTrend >= 0 ? '+' . number_format($monthlyTrend, 1) . '% from last month' : number_format($monthlyTrend, 1) . '% from last month')
                ->descriptionIcon($monthlyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Yearly Deposits', 'Rp ' . number_format($currentYearDeposit, 0, ',', '.'))
                ->description($yearlyTrend >= 0 ? '+' . number_format($yearlyTrend, 1) . '% from last year' : number_format($yearlyTrend, 1) . '% from last year')
                ->descriptionIcon($yearlyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($yearlyTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Current Balance', 'Rp ' . number_format($lastBalance, 0, ',', '.'))
                ->description('Last updated: ' . Carbon::parse(Balance::latest()->first()?->date)->diffForHumans())
                ->color($lastBalance > 1000000 ? 'success' : 'danger'),
        ];
    }
}

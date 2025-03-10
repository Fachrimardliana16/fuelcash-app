<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CurrentBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get current balance
        $currentBalance = Balance::latest()->first()?->remaining_balance ?? 0;

        // Calculate balance trend percentage over the last 30 days
        $lastMonthDeposit = Balance::where('date', '>=', Carbon::now()->subDays(30))
            ->sum('deposit_amount');

        $lastMonthSpending = Transaction::where('usage_date', '>=', Carbon::now()->subDays(30))
            ->sum('amount');

        $balanceChange = $lastMonthDeposit - $lastMonthSpending;
        $trend = $currentBalance > 0 ? ($balanceChange / $currentBalance * 100) : 0;
        $trendIcon = $trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $trendColor = $trend >= 0 ? 'success' : 'danger';
        $trendFormatted = number_format(abs($trend), 1) . '%';

        // Get last deposit info
        $lastDeposit = Balance::latest()->first();

        // Fix the date handling - convert string date to Carbon instance first
        $lastDepositDate = 'Never';
        if ($lastDeposit && $lastDeposit->date) {
            // Convert string date to Carbon instance
            $lastDepositDate = Carbon::parse($lastDeposit->date)->diffForHumans();
        }

        $lastDepositAmount = $lastDeposit ? 'Rp ' . number_format($lastDeposit->deposit_amount, 0, ',', '.') : 'None';

        return [
            Stat::make('Current Balance', 'Rp ' . number_format($currentBalance, 0, ',', '.'))
                ->description($trend >= 0 ? 'Increased by ' . $trendFormatted : 'Decreased by ' . $trendFormatted)
                ->descriptionIcon($trendIcon)
                ->color($trendColor)
                ->chart([$currentBalance - $balanceChange, $currentBalance]),

            Stat::make('Last Deposit', $lastDepositAmount)
                ->description($lastDepositDate)
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\FuelType;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class BalanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $pollingInterval = '15s';

    protected function getFontSize($number)
    {
        $length = strlen((string)$number);
        if ($length > 12) return 'text-lg';
        if ($length > 9) return 'text-xl';
        if ($length > 6) return 'text-2xl';
        return 'text-3xl';
    }

    protected function getStats(): array
    {
        // Get all active fuel types
        $fuelTypes = FuelType::where('isactive', true)->get();

        $stats = [];

        // Add stats for each fuel type
        foreach ($fuelTypes as $fuelType) {
            // Get the latest balance for this fuel type
            $latestBalance = Balance::where('fuel_type_id', $fuelType->id)
                ->latest()
                ->first();

            // Get the remaining balance for this fuel type
            $remainingBalance = $latestBalance ? $latestBalance->remaining_balance : 0;

            // Use max_deposit value from the FuelType model
            $maxBalance = $fuelType->max_deposit;

            // Calculate balance percentage
            $percentageFilled = $maxBalance > 0 ? min(100, ($remainingBalance / $maxBalance) * 100) : 0;

            // Get average daily consumption based on last week
            $last7DaysConsumption = Transaction::where('fuel_id', $fuelType->fuel_id)
                ->where('usage_date', '>=', now()->subDays(7))
                ->sum('amount');

            $avgDailyConsumption = $last7DaysConsumption > 0 ? ($last7DaysConsumption / 7) : 0;

            // Estimate days remaining based on average daily consumption
            $daysRemaining = $avgDailyConsumption > 0 ? round($remainingBalance / $avgDailyConsumption) : null;

            // Determine color based on percentage
            $color = 'danger';
            if ($percentageFilled > 50) {
                $color = 'success';
            } elseif ($percentageFilled > 20) {
                $color = 'warning';
            }

            // Create a simple clear description
            $description = new HtmlString(
                ($daysRemaining !== null
                    ? "<strong>{$daysRemaining} hari</strong> tersisa"
                    : "Belum ada data konsumsi") .
                    " · " . number_format($percentageFilled, 0) . "%"
            );

            $formattedBalance = number_format($remainingBalance, 0, ',', '.');
            $fontSize = $this->getFontSize($remainingBalance);

            // Create a stat for this fuel type
            $stats[] = Stat::make(
                $fuelType->name,
                new HtmlString("<span class='{$fontSize}'>Rp {$formattedBalance}</span>")
            )
                ->description($description)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([$percentageFilled, 100 - $percentageFilled])
                ->color($color);
        }

        return $stats;
    }
}

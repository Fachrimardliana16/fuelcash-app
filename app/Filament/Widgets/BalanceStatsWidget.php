<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\FuelType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class BalanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $pollingInterval = '10s';

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

            // Get the date of the last deposit
            $lastDepositDate = $latestBalance ? Carbon::parse($latestBalance->date)->format('d M Y') : 'Belum ada deposit';

            // Use max_deposit value from the FuelType model
            $maxBalance = $fuelType->max_deposit;

            // Calculate balance percentage
            $percentageFilled = $maxBalance > 0 ? min(100, ($remainingBalance / $maxBalance) * 100) : 0;

            // Determine color based on percentage
            $color = 'danger';
            if ($percentageFilled > 50) {
                $color = 'success';
            } elseif ($percentageFilled > 20) {
                $color = 'warning';
            }

            // Create a stat for this fuel type with simpler description
            $stats[] = Stat::make('Saldo ' . $fuelType->name, 'Rp ' . number_format($remainingBalance, 0, ',', '.'))
                ->description("$percentageFilled% dari maksimal · Update: $lastDepositDate")
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([$percentageFilled, 100 - $percentageFilled])
                ->color($color);
        }

        return $stats;
    }
}

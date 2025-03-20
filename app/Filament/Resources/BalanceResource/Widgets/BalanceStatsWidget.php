<?php

namespace App\Filament\Resources\BalanceResource\Widgets;

use App\Models\Balance;
use App\Models\FuelType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class BalanceStatsWidget extends BaseWidget
{
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

            // Create a stat for this fuel type
            $stats[] = Stat::make($fuelType->name . ' Sisa Saldo :', 'Rp ' . number_format($remainingBalance, 0, ',', '.'))
                ->description(new HtmlString('Maksimal: Rp ' . number_format($maxBalance, 0, ',', '.') . '<br>Deposit terakhir: ' . $lastDepositDate))
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->chart([round($percentageFilled, 1), 100 - round($percentageFilled, 1)])
                ->color($color);
        }

        return $stats;
    }
}

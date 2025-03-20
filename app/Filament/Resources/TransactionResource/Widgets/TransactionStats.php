<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use App\Models\Balance;
use App\Models\FuelType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class TransactionStats extends BaseWidget
{
    // Hapus polling interval
    // protected static ?string $pollingInterval = '10s';

    protected int | string | array $columnSpan = 'full';

    #[On('transaction-created')]
    public function refresh(): void
    {
        $this->refreshStats();
    }

    protected function getStats(): array
    {
        $stats = [];
        $fuelTypes = FuelType::where('isactive', true)->get();
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth();

        // Stats per fuel type only
        foreach ($fuelTypes as $fuelType) {
            $stats[] = $this->createCombinedFuelTypeStats(
                $fuelType,
                $currentMonth,
                Carbon::now()->year,
                $lastMonth->month,
                $lastMonth->year
            );
        }

        return $stats;
    }

    // Remove createOverviewStats method as it's no longer needed

    protected function createCombinedFuelTypeStats(
        FuelType $fuelType,
        int $currentMonth,
        int $currentYear,
        int $lastMonth,
        int $lastYear
    ): Stat {
        // Get latest balance
        $latestBalance = Balance::where('fuel_type_id', $fuelType->id)
            ->latest()
            ->first();

        // Current month expenses and volume
        $currentMonthExpense = Transaction::where('fuel_type_id', $fuelType->id)
            ->whereMonth('usage_date', $currentMonth)
            ->whereYear('usage_date', $currentYear)
            ->sum('amount');

        $totalVolume = Transaction::where('fuel_type_id', $fuelType->id)
            ->whereMonth('usage_date', $currentMonth)
            ->whereYear('usage_date', $currentYear)
            ->sum('volume');

        // Count transactions
        $totalTransactions = Transaction::where('fuel_type_id', $fuelType->id)
            ->whereMonth('usage_date', $currentMonth)
            ->whereYear('usage_date', $currentYear)
            ->count();

        $remainingBalance = $latestBalance?->remaining_balance ?? 0;

        return Stat::make(
            label: "⛽ {$fuelType->name}",
            value: 'Rp ' . number_format($remainingBalance, 0, ',', '.')
        )
            ->description(new HtmlString(
                "Total Transaksi: {$totalTransactions}x" .
                '<br>Pengeluaran: Rp ' . number_format($currentMonthExpense, 0, ',', '.') .
                '<br>Volume: ' . number_format($totalVolume, 2, ',', '.') . ' Liter'
            ))
            ->color($this->getStatusColor($remainingBalance))
            ->chart($this->generateChartData($fuelType->id));
    }

    protected function calculatePercentageChange($old, $new): float
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / $old) * 100, 1);
    }

    protected function calculateTrend($current, $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function getBalanceIcon($balance): string
    {
        if ($balance > 5000000) return 'heroicon-o-check-circle';
        if ($balance > 2000000) return 'heroicon-o-exclamation-circle';
        return 'heroicon-o-x-circle';
    }

    protected function getStatusColor($balance): string
    {
        if ($balance > 5000000) return 'success';
        if ($balance > 2000000) return 'warning';
        return 'danger';
    }

    protected function getBalanceStatusColor($percentageUsed): string
    {
        if ($percentageUsed > 75) return 'success';
        if ($percentageUsed > 25) return 'warning';
        return 'danger';
    }

    protected function generateChartData(?int $fuelTypeId = null): array
    {
        $days = collect(range(1, 7))->map(function ($day) use ($fuelTypeId) {
            $query = Transaction::whereDate('created_at', Carbon::now()->subDays($day));

            if ($fuelTypeId) {
                $query->where('fuel_type_id', $fuelTypeId);
            }

            return $query->count();
        })->toArray();

        return array_reverse($days);
    }
}

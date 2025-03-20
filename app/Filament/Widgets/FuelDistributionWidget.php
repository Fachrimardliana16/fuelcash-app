<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Distribusi BBM Bulan Ini';
    protected static ?string $maxHeight = '240px';

    protected function getData(): array
    {
        $fuelData = Transaction::query()
            ->join('fuels', 'transactions.fuel_id', '=', 'fuels.id')
            ->whereMonth('usage_date', Carbon::now()->month)
            ->whereYear('usage_date', Carbon::now()->year)
            ->select('fuels.name', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('fuels.id', 'fuels.name')
            ->orderByDesc('total_amount')
            ->get();

        // Define specific colors for common fuel types with simpler colors
        $colorMap = [
            'Pertalite' => '#4ade80', // green
            'Pertamax' => '#f97316',  // orange
            'Solar' => '#3b82f6',     // blue
            'Dexlite' => '#8b5cf6',   // purple
            'Pertamax Turbo' => '#ec4899', // pink
        ];

        $colors = [];
        foreach ($fuelData as $fuel) {
            $colors[] = $colorMap[$fuel->name] ?? ('hsl(' . (count($colors) * 50 % 360) . ', 70%, 60%)');
        }

        // Calculate percentages for labels
        $total = $fuelData->sum('total_amount');
        $labels = $fuelData->map(function ($item) use ($total) {
            $percentage = $total > 0 ? round(($item->total_amount / $total) * 100) : 0;
            return $item->name . ' (' . $percentage . '%)';
        })->toArray();

        return [
            'datasets' => [
                [
                    'data' => $fuelData->pluck('total_amount')->toArray(),
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'font' => [
                            'size' => 11,
                        ],
                        'padding' => 15,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(context.raw);
                        }",
                    ],
                ],
            ],
        ];
    }
}

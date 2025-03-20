<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Distribusi Jenis BBM Bulan Ini';

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

        // Define specific colors for common fuel types
        $colorMap = [
            'Pertalite' => 'rgb(75, 192, 192)',
            'Pertamax' => 'rgb(255, 159, 64)',
            'Solar' => 'rgb(54, 162, 235)',
            'Dexlite' => 'rgb(153, 102, 255)',
            'Pertamax Turbo' => 'rgb(255, 99, 132)',
        ];

        $colors = [];
        foreach ($fuelData as $fuel) {
            $colors[] = $colorMap[$fuel->name] ?? ('hsl(' . (count($colors) * 50 % 360) . ', 70%, 60%)');
        }

        return [
            'datasets' => [
                [
                    'data' => $fuelData->pluck('total_amount')->toArray(),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $fuelData->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            return label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                        }",
                    ],
                ],
            ],
        ];
    }
}

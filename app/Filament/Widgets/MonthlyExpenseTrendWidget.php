<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyExpenseTrendWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Tren Pengeluaran 6 Bulan Terakhir';

    protected function getData(): array
    {
        // Get data for the last 6 months
        $data = [];
        $labels = [];
        $colors = [];

        // Get monthly totals
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $monthlyTotal = Transaction::whereYear('usage_date', $date->year)
                ->whereMonth('usage_date', $date->month)
                ->sum('amount');

            $data[] = $monthlyTotal;

            // Current month is highlighted
            if ($i === 0) {
                $colors[] = 'rgba(59, 130, 246, 0.8)'; // Highlighted color
            } else {
                $colors[] = 'rgba(59, 130, 246, 0.5)';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                        }",
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }",
                    ],
                ],
            ],
        ];
    }
}

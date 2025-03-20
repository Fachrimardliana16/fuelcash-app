<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyExpenseTrendWidget extends ChartWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Tren Pengeluaran 6 Bulan';
    protected static ?string $maxHeight = '240px';

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
                $colors[] = 'rgba(59, 130, 246, 0.9)'; // Current month - blue
            } else {
                $colors[] = 'rgba(156, 163, 175, 0.6)'; // Past months - gray
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
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(context.parsed.y);
                        }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                            }
                            return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                        }",
                    ],
                ],
            ],
        ];
    }
}

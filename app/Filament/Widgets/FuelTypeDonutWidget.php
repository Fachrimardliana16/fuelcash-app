<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelTypeDonutWidget extends ChartWidget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Penggunaan BBM';
    protected static ?string $maxHeight = '300px';

    // Filter properties
    public ?string $dateRange = 'month';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        // Apply date filter
        $query = Transaction::query()
            ->join('fuels', 'transactions.fuel_id', '=', 'fuels.id');

        switch ($this->dateRange) {
            case 'week':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfMonth());
                break;
            case 'year':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfYear());
                break;
            case 'quarter':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfQuarter());
                break;
        }

        $fuelData = $query
            ->select('fuels.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('fuels.id', 'fuels.name')
            ->orderByDesc('count')
            ->get();

        // Define specific colors for common fuel types
        $colorMap = [
            'Pertalite' => '#4ade80', // green
            'Pertamax' => '#f97316', // orange
            'Solar' => '#3b82f6', // blue
            'Dexlite' => '#8b5cf6', // purple
            'Pertamax Turbo' => '#ec4899', // pink
        ];

        $colors = [];
        foreach ($fuelData as $fuel) {
            $colors[] = $colorMap[$fuel->name] ?? ('hsl(' . (count($colors) * 50 % 360) . ', 70%, 60%)');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $fuelData->pluck('count')->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $fuelData->pluck('name')->toArray(),
        ];
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
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }",
                    ],
                ],
            ],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('dateRange')
                ->label('Periode')
                ->options([
                    'week' => 'Minggu Ini',
                    'month' => 'Bulan Ini',
                    'quarter' => 'Kuartal Ini',
                    'year' => 'Tahun Ini',
                ])
                ->default('month')
                ->live()
                ->afterStateUpdated(fn() => $this->refreshChart()),
        ];
    }
}

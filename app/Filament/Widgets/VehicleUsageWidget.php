<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\VehicleType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleUsageWidget extends ChartWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Pola Penggunaan Kendaraan';
    protected static ?string $pollingInterval = null;

    public ?string $timeframe = 'week';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'quarter' => 'Kuartal Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        // Get the start date based on the selected timeframe
        $startDate = match ($this->filter) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek(),
        };

        // Get transactions data group by day of week and vehicle type
        $vehicleTypes = VehicleType::where('isactive', true)->pluck('name', 'id')->toArray();

        $daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Get daily usage by vehicle type
        $dailyUsage = Transaction::where('usage_date', '>=', $startDate)
            ->select(
                'vehicle_type_id',
                DB::raw('DAYOFWEEK(usage_date) as day_of_week'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('vehicle_type_id', 'day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('vehicle_type_id');

        // Prepare datasets
        $datasets = [];
        $colors = [
            '#FF6384', // red
            '#36A2EB', // blue
            '#FFCE56', // yellow
            '#4BC0C0', // teal
            '#9966FF', // purple
            '#FF9F40', // orange
            '#C9CBCF', // gray
        ];

        $colorIndex = 0;
        foreach ($vehicleTypes as $id => $typeName) {
            $data = array_fill(0, 7, 0); // Initialize with zeros for all days

            if (isset($dailyUsage[$id])) {
                foreach ($dailyUsage[$id] as $usage) {
                    // DAYOFWEEK() returns 1 for Sunday, 2 for Monday, etc.
                    // We need to adjust to our array (0 for Monday, 6 for Sunday)
                    $dayIndex = ($usage->day_of_week + 5) % 7;
                    $data[$dayIndex] = $usage->transaction_count;
                }
            }

            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;

            $datasets[] = [
                'label' => $typeName,
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => $color . '40', // add transparency
                'borderWidth' => 2,
                'fill' => true,
            ];
        }

        return [
            'labels' => $daysOfWeek,
            'datasets' => $datasets,
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.1,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            const label = context.dataset.label || '';
                            const value = context.raw || 0;
                            return label + ': ' + value + ' transaksi';
                        }",
                    ],
                ],
            ],
        ];
    }
}

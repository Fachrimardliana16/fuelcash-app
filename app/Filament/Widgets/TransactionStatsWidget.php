<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionStatsWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'half';

    // Use this property to define how filters should be applied
    protected static string $defaultFilterKey = 'period';

    public function getHeading(): ?string
    {
        return 'Statistik Transaksi';
    }

    protected function getFilters(): ?array
    {
        return [
            'hourly' => 'Per Jam (24 jam terakhir)',
            'daily' => 'Harian (30 hari terakhir)',
            'monthly' => 'Bulanan (12 bulan terakhir)',
            'custom_range' => 'Rentang Khusus Tanggal',
        ];
    }

    protected function getData(): array
    {
        // Use $this->filter to access the currently selected filter
        $filter = $this->filter;

        if ($filter === 'custom_range') {
            // For custom range, we need to get the date range from the form data
            $startDate = $this->filterFormData['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = $this->filterFormData['end_date'] ?? Carbon::now()->format('Y-m-d');

            return $this->getCustomRangeData($startDate, $endDate);
        }

        // Otherwise, use the filter value to determine which data to show
        switch ($filter) {
            case 'hourly':
                return $this->getHourlyData();
            case 'daily':
                return $this->getDailyData();
            case 'monthly':
                return $this->getMonthlyData();
            default:
                return $this->getDailyData();
        }
    }

    // Add extra form components for the custom range
    protected function getFilterForm(): array
    {
        return [
            DatePicker::make('start_date')
                ->visible(fn(callable $get, $set) => $this->filter === 'custom_range')
                ->default(today()->subDays(30))
                ->reactive(),

            DatePicker::make('end_date')
                ->visible(fn(callable $get, $set) => $this->filter === 'custom_range')
                ->default(today())
                ->reactive(),
        ];
    }

    // Show the specific form when custom range is selected
    public function filterFormVisible(): bool
    {
        return $this->filter === 'custom_range';
    }

    protected function getDailyData(): array
    {
        $transactions = Transaction::selectRaw('DATE(usage_date) as date, SUM(amount) as total')
            ->whereDate('usage_date', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Create a range of days for the past month
        $period = Carbon::now()->subDays(30)->daysUntil(Carbon::now());

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $date->format('d M');

            $amount = $transactions->firstWhere('date', $formattedDate)?->total ?? 0;
            $data[] = $amount;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi Harian',
                    'data' => $data,
                    'borderColor' => '#36A2EB',
                    'fill' => false,
                ]
            ],
        ];
    }

    protected function getMonthlyData(): array
    {
        $transactions = Transaction::selectRaw('YEAR(usage_date) as year, MONTH(usage_date) as month, SUM(amount) as total')
            ->whereDate('usage_date', '>=', Carbon::now()->subMonths(12)->startOfMonth())
            ->groupBy(['year', 'month'])
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        // Create a range of the last 12 months
        $period = Carbon::now()->subMonths(11)->startOfMonth()->monthsUntil(Carbon::now()->endOfMonth());

        foreach ($period as $date) {
            $year = $date->year;
            $month = $date->month;
            $labels[] = $date->format('M Y');

            $monthData = $transactions->first(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });

            $data[] = $monthData?->total ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi Bulanan',
                    'data' => $data,
                    'borderColor' => '#4BC0C0',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                ]
            ],
        ];
    }

    protected function getYearlyData(): array
    {
        $transactions = Transaction::selectRaw('YEAR(usage_date) as year, SUM(amount) as total')
            ->whereDate('usage_date', '>=', Carbon::now()->subYears(5)->startOfYear())
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $labels = [];
        $data = [];

        // Create a range of the last 5 years
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 4;

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $labels[] = (string) $year;

            $yearData = $transactions->firstWhere('year', $year);
            $data[] = $yearData?->total ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi Tahunan',
                    'data' => $data,
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                ]
            ],
        ];
    }

    protected function getCustomRangeData($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Determine appropriate grouping based on date range
        $diffInDays = $start->diffInDays($end);

        if ($diffInDays > 90) {
            // For ranges longer than 3 months, group by month
            $transactions = Transaction::selectRaw('YEAR(usage_date) as year, MONTH(usage_date) as month, SUM(amount) as total')
                ->whereDate('usage_date', '>=', $start)
                ->whereDate('usage_date', '<=', $end)
                ->groupBy(['year', 'month'])
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $labels = [];
            $data = [];

            // Generate all months in range
            $period = $start->copy()->startOfMonth()->monthsUntil($end->copy()->endOfMonth());

            foreach ($period as $date) {
                $year = $date->year;
                $month = $date->month;
                $labels[] = $date->format('M Y');

                $monthData = $transactions->first(function ($item) use ($year, $month) {
                    return $item->year == $year && $item->month == $month;
                });

                $data[] = $monthData?->total ?? 0;
            }
        } else {
            // For shorter ranges, group by day
            $transactions = Transaction::selectRaw('DATE(usage_date) as date, SUM(amount) as total')
                ->whereDate('usage_date', '>=', $start)
                ->whereDate('usage_date', '<=', $end)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $data = [];

            // Generate all days in range
            $period = $start->daysUntil($end);

            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $labels[] = $date->format('d M');

                $dayData = $transactions->firstWhere('date', $formattedDate);
                $data[] = $dayData?->total ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaction Amount',
                    'data' => $data,
                    'borderColor' => '#9966FF',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                ]
            ],
        ];
    }

    protected function getHourlyData(): array
    {
        $transactions = Transaction::selectRaw('DATE_FORMAT(usage_date, "%Y-%m-%d %H:00:00") as hour, SUM(amount) as total')
            ->where('usage_date', '>=', Carbon::now()->subHours(24))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];

        // Create a range of hours for the past 24 hours
        $period = Carbon::now()->subHours(24)->hoursUntil(Carbon::now());

        foreach ($period as $hour) {
            $formattedHour = $hour->format('Y-m-d H:00:00');
            $labels[] = $hour->format('H:00');

            $amount = $transactions->firstWhere('hour', $formattedHour)?->total ?? 0;
            $data[] = $amount;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Transaksi Per Jam',
                    'data' => $data,
                    'borderColor' => '#FF9F40',
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'fill' => false,
                ]
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                        'drawBorder' => true,
                    ],
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }",
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3, // Makes the line a bit smoother
                    'borderWidth' => 2,
                ],
                'point' => [
                    'radius' => 3,
                    'hitRadius' => 10,
                    'hoverRadius' => 5,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

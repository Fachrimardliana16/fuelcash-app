<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\FuelType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionStatsWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $maxHeight = '300px';

    // Use this property to define how filters should be applied
    protected static string $defaultFilterKey = 'period';

    // Add fuel type filter
    public ?string $fuelFilter = 'all';

    public function getHeading(): ?string
    {
        return 'Statistik Transaksi';
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian (30 hari terakhir)',
            'monthly' => 'Bulanan (12 bulan terakhir)',
            'yearlyComparison' => 'Perbandingan Tahunan',
            'custom_range' => 'Rentang Kustom',
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

        // For yearly comparison
        if ($filter === 'yearlyComparison') {
            return $this->getYearlyComparisonData();
        }

        // Otherwise, use the filter value to determine which data to show
        return match ($filter) {
            'monthly' => $this->getMonthlyData(),
            default => $this->getDailyData(),
        };
    }

    // Add extra form components for the custom range
    protected function getFilterForm(): array
    {
        $fuelTypes = FuelType::where('isactive', true)->pluck('name', 'id')->toArray();
        $fuelTypes = ['all' => 'Semua Jenis BBM'] + $fuelTypes;

        return [
            DatePicker::make('start_date')
                ->visible(fn(callable $get, $set) => $this->filter === 'custom_range')
                ->default(today()->subDays(30))
                ->reactive(),

            DatePicker::make('end_date')
                ->visible(fn(callable $get, $set) => $this->filter === 'custom_range')
                ->default(today())
                ->reactive(),

            Select::make('fuelFilter')
                ->label('Jenis BBM')
                ->options($fuelTypes)
                ->default('all')
                ->reactive()
                ->afterStateUpdated(fn() => $this->refreshChart()),
        ];
    }

    // Show the specific form when custom range is selected
    public function filterFormVisible(): bool
    {
        return true; // Always show the form for fuel type filter
    }

    protected function getDailyData(): array
    {
        $query = Transaction::query()
            ->whereDate('usage_date', '>=', Carbon::now()->subDays(30));

        // Apply fuel filter if needed
        if ($this->fuelFilter !== 'all') {
            $query->where('fuel_id', $this->fuelFilter);
        }

        $transactions = $query->selectRaw('DATE(usage_date) as date, SUM(amount) as total')
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
                    'borderColor' => '#4ade80',
                    'backgroundColor' => 'rgba(74, 222, 128, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                ]
            ],
        ];
    }

    protected function getMonthlyData(): array
    {
        $query = Transaction::query()
            ->whereDate('usage_date', '>=', Carbon::now()->subMonths(12)->startOfMonth());

        // Apply fuel filter if needed
        if ($this->fuelFilter !== 'all') {
            $query->where('fuel_id', $this->fuelFilter);
        }

        $transactions = $query->selectRaw('YEAR(usage_date) as year, MONTH(usage_date) as month, SUM(amount) as total')
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
        $query = Transaction::query()
            ->whereDate('usage_date', '>=', Carbon::now()->subYears(5)->startOfYear());

        // Apply fuel filter if needed
        if ($this->fuelFilter !== 'all') {
            $query->where('fuel_id', $this->fuelFilter);
        }

        $transactions = $query->selectRaw('YEAR(usage_date) as year, SUM(amount) as total')
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
            $query = Transaction::query()
                ->whereDate('usage_date', '>=', $start)
                ->whereDate('usage_date', '<=', $end);

            // Apply fuel filter if needed
            if ($this->fuelFilter !== 'all') {
                $query->where('fuel_id', $this->fuelFilter);
            }

            $transactions = $query->selectRaw('YEAR(usage_date) as year, MONTH(usage_date) as month, SUM(amount) as total')
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
            $query = Transaction::query()
                ->whereDate('usage_date', '>=', $start)
                ->whereDate('usage_date', '<=', $end);

            // Apply fuel filter if needed
            if ($this->fuelFilter !== 'all') {
                $query->where('fuel_id', $this->fuelFilter);
            }

            $transactions = $query->selectRaw('DATE(usage_date) as date, SUM(amount) as total')
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
        $query = Transaction::query()
            ->where('usage_date', '>=', Carbon::now()->subHours(24));

        // Apply fuel filter if needed
        if ($this->fuelFilter !== 'all') {
            $query->where('fuel_id', $this->fuelFilter);
        }

        $transactions = $query->selectRaw('DATE_FORMAT(usage_date, "%Y-%m-%d %H:00:00") as hour, SUM(amount) as total')
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

    protected function getYearlyComparisonData(): array
    {
        // Get current year and previous year data for comparison
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        $query = Transaction::query();

        // Apply fuel filter if needed
        if ($this->fuelFilter !== 'all') {
            $query->where('fuel_id', $this->fuelFilter);
        }

        $currentYearData = (clone $query)
            ->whereYear('usage_date', $currentYear)
            ->selectRaw('MONTH(usage_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $previousYearData = (clone $query)
            ->whereYear('usage_date', $previousYear)
            ->selectRaw('MONTH(usage_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $currentYearValues = [];
        $previousYearValues = [];

        for ($i = 1; $i <= 12; $i++) {
            $currentYearValues[] = $currentYearData[$i] ?? 0;
            $previousYearValues[] = $previousYearData[$i] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => "Tahun $currentYear",
                    'data' => $currentYearValues,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => "Tahun $previousYear",
                    'data' => $previousYearValues,
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => true,
                ]
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
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
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'drawBorder' => false,
                    ],
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

    protected function getType(): string
    {
        return 'line';
    }
}

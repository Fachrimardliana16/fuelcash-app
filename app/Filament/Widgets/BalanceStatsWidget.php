<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class BalanceStatsWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'half';

    // Use this property to define how filters should be applied
    protected static string $defaultFilterKey = 'period';

    // Track if summary is visible
    public bool $showSummary = false;

    // Store summary data
    public array $summaryData = [];

    public function getHeading(): ?string
    {
        return 'Saldo Kas BBM';
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $data = match ($filter) {
            'daily' => $this->getDailyData(),
            'monthly' => $this->getMonthlyData(),
            'yearly' => $this->getYearlyData(),
            default => $this->getDailyData(),
        };

        // Store the full data for the summary
        $this->summaryData = $data['summary'] ?? [];

        return [
            'labels' => $data['labels'],
            'datasets' => [
                [
                    'label' => 'Deposits',
                    'data' => $data['depositData'],
                    'backgroundColor' => '#4ade80', // Simple green color
                    'borderColor' => '#16a34a',
                    'borderWidth' => 1,
                ]
            ],
        ];
    }

    // New method for daily data (last 30 days)
    protected function getDailyData(): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29); // Last 30 days including today

        return $this->getBalanceDataForRange($startDate, $endDate, 'day');
    }

    // Renamed from getMonthData to getMonthlyData (last 12 months)
    protected function getMonthlyData(): array
    {
        $endDate = Carbon::now()->endOfMonth();
        $startDate = Carbon::now()->subMonths(11)->startOfMonth(); // Last 12 months

        return $this->getBalanceDataForRange($startDate, $endDate, 'month');
    }

    // Renamed from getYearData to getYearlyData (last 5 years)
    protected function getYearlyData(): array
    {
        $endDate = Carbon::now()->endOfYear();
        $startDate = Carbon::now()->subYears(4)->startOfYear(); // Last 5 years

        return $this->getBalanceDataForRange($startDate, $endDate, 'year');
    }

    protected function getBalanceDataForRange(Carbon $startDate, Carbon $endDate, string $groupBy): array
    {
        // Get all balances within the range
        $balances = Balance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        $labels = [];
        $depositData = [];
        $summary = [];

        // Group data according to the grouping parameter
        if ($groupBy === 'day') {
            $periodRange = $startDate->copy()->daysUntil($endDate);
            $format = 'd M';

            foreach ($periodRange as $date) {
                $currentDate = $date->format('Y-m-d');
                $formattedDate = $date->format($format);

                // Calculate daily deposits
                $dailyDeposits = $balances->where('date', $currentDate)->sum('deposit_amount');

                // Add data points
                $labels[] = $formattedDate;
                $depositData[] = $dailyDeposits;

                // Add to summary if there are deposits
                if ($dailyDeposits > 0) {
                    $summary[] = [
                        'date' => $formattedDate,
                        'amount' => $dailyDeposits,
                    ];
                }
            }
        } elseif ($groupBy === 'month') {
            $periodRange = $startDate->copy()->startOfMonth()->monthsUntil($endDate->copy()->endOfMonth());

            foreach ($periodRange as $date) {
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                $formattedDate = $date->format('M Y');

                // Calculate monthly deposits
                $monthlyDeposits = $balances->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])->sum('deposit_amount');

                // Add data points
                $labels[] = $formattedDate;
                $depositData[] = $monthlyDeposits;

                // Add to summary if there are deposits
                if ($monthlyDeposits > 0) {
                    $summary[] = [
                        'date' => $formattedDate,
                        'amount' => $monthlyDeposits,
                    ];
                }
            }
        } elseif ($groupBy === 'year') {
            $startYear = (int)$startDate->format('Y');
            $endYear = (int)$endDate->format('Y');

            for ($year = $startYear; $year <= $endYear; $year++) {
                $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfDay();
                $formattedDate = (string)$year;

                // Calculate yearly deposits
                $yearlyDeposits = $balances->whereBetween('date', [$yearStart->format('Y-m-d'), $yearEnd->format('Y-m-d')])->sum('deposit_amount');

                // Add data points
                $labels[] = $formattedDate;
                $depositData[] = $yearlyDeposits;

                // Add to summary if there are deposits
                if ($yearlyDeposits > 0) {
                    $summary[] = [
                        'date' => $formattedDate,
                        'amount' => $yearlyDeposits,
                    ];
                }
            }
        }

        return [
            'labels' => $labels,
            'depositData' => $depositData,
            'summary' => $summary,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 1)', // White grid lines
                    ],
                    'ticks' => [
                        'color' => '#333333', // Darker text for visibility
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                        'callback' => "function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + ' M';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + ' K';
                            } else {
                                return 'Rp ' + value;
                            }
                        }",
                    ],
                ],
                'x' => [
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 1)', // White grid lines
                    ],
                    'ticks' => [
                        'color' => '#333333', // Darker text for visibility
                        'font' => [
                            'size' => 12,
                        ],
                        'maxRotation' => 45, // Angled labels for better fit
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => 'rgba(0, 0, 0, 0.1)',
                    'borderWidth' => 1,
                    'padding' => 10,
                    'displayColors' => true,
                    'callbacks' => [
                        'title' => "function(tooltipItems) {
                            return tooltipItems[0].label || '';
                        }",
                        'label' => "function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                            return label;
                        }",
                    ],
                ],
                // Add plugin to display values on each bar
                'datalabels' => [
                    'display' => true,
                    'color' => '#333333',
                    'anchor' => 'end',
                    'align' => 'top',
                    'offset' => 0,
                    'font' => [
                        'weight' => 'bold',
                        'size' => 11,
                    ],
                    'formatter' => "function(value) {
                        if (value === 0) return '';
                        if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' M';
                        } else if (value >= 1000) {
                            return 'Rp ' + (value / 1000).toFixed(0) + ' K';
                        } else {
                            return 'Rp ' + value;
                        }
                    }",
                ],
            ],
            'onClick' => "function(event, elements) {
                window.livewire.find('" . $this->getId() . "').toggleSummary();
            }",
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function toggleSummary()
    {
        $this->showSummary = !$this->showSummary;
    }

    protected function getFooter(): ?string
    {
        if (!$this->showSummary || empty($this->summaryData)) {
            return '<div class="text-xs text-center text-gray-500 mt-2">Click on the chart to show/hide summary</div>';
        }

        $totalDeposit = 0;
        $html = '<div class="p-3 bg-gray-50 rounded-lg mt-2">';
        $html .= '<h4 class="text-md font-medium mb-2">Deposit Summary</h4>';
        $html .= '<div class="overflow-x-auto">';
        $html .= '<table class="min-w-full divide-y divide-gray-200">';
        $html .= '<thead><tr>';
        $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>';
        $html .= '<th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody class="divide-y divide-gray-200">';

        foreach ($this->summaryData as $item) {
            $totalDeposit += $item['amount'];
            $formattedAmount = 'Rp ' . number_format($item['amount'], 0, ',', '.');
            $html .= '<tr>';
            $html .= '<td class="px-3 py-2 text-sm text-gray-900">' . $item['date'] . '</td>';
            $html .= '<td class="px-3 py-2 text-sm text-gray-900 text-right">' . $formattedAmount . '</td>';
            $html .= '</tr>';
        }

        $formattedTotal = 'Rp ' . number_format($totalDeposit, 0, ',', '.');
        $html .= '<tr class="bg-gray-100">';
        $html .= '<td class="px-3 py-2 text-sm font-medium text-gray-900">Total</td>';
        $html .= '<td class="px-3 py-2 text-sm font-medium text-gray-900 text-right">' . $formattedTotal . '</td>';
        $html .= '</tr>';

        $html .= '</tbody></table></div></div>';

        return $html;
    }
}

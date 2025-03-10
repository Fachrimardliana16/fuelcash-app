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

    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Bahan Bakar Paling Sering Digunakan';
    protected static ?string $pollingInterval = null;

    // Filter properties
    public ?string $dateRange = 'all';

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dateRange')
                    ->options([
                        'all' => 'All Time',
                        'today' => 'Today',
                        'week' => 'This Week',
                        'month' => 'This Month',
                        'year' => 'This Year',
                    ])
                    ->default('all')
                    ->live(),
            ]);
    }

    protected function getData(): array
    {
        // Apply date filter
        $query = Transaction::query()
            ->join('fuels', 'transactions.fuel_id', '=', 'fuels.id');

        switch ($this->dateRange) {
            case 'today':
                $query->whereDate('usage_date', Carbon::today());
                break;
            case 'week':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfMonth());
                break;
            case 'year':
                $query->whereDate('usage_date', '>=', Carbon::now()->startOfYear());
                break;
        }

        $fuelData = $query
            ->select('fuels.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('fuels.id', 'fuels.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Generate more pleasing colors for the chart
        $colors = [];
        for ($i = 0; $i < $fuelData->count(); $i++) {
            $hue = ($i * 360 / $fuelData->count()) % 360;
            $colors[] = "hsl($hue, 70%, 60%)";
        }

        return [
            'datasets' => [
                [
                    'label' => 'Fuel Types',
                    'data' => $fuelData->pluck('count')->toArray(),
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $fuelData->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('dateRange')
                ->options([
                    'all' => 'All Time',
                    'today' => 'Today',
                    'week' => 'This Week',
                    'month' => 'This Month',
                    'year' => 'This Year',
                ])
                ->default('all')
                ->live()
                ->afterStateUpdated(fn() => $this->refreshChart()),
        ];
    }
}

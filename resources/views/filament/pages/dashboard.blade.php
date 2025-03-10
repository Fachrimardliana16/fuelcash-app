<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            {{ \App\Filament\Widgets\CurrentBalanceWidget::make(['columnSpan' => 'full'])->render() }}
        </div>
        <div class="lg:col-span-2">
            {{ \App\Filament\Widgets\TransactionStatsWidget::make(['columnSpan' => 'full'])->render() }}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-1">
            {{ \App\Filament\Widgets\FuelTypeDonutWidget::make(['columnSpan' => 'full'])->render() }}
        </div>
        <div class="lg:col-span-2">
            {{ \App\Filament\Widgets\MonthlyTransactionsChart::make(['columnSpan' => 'full'])->render() }}
        </div>
    </div>

    <div class="mt-6">
        {{ \App\Filament\Widgets\TopVehiclesWidget::make(['columnSpan' => 'full'])->render() }}
    </div>

    <div class="mt-6">
        {{ \App\Filament\Widgets\LatestTransactionsWidget::make(['columnSpan' => 'full'])->render() }}
    </div>

    <div class="mt-6">
        {{ \App\Filament\Widgets\LatestBalancesWidget::make(['columnSpan' => 'full'])->render() }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div>
            {{ \Filament\Widgets\AccountWidget::make(['columnSpan' => 1])->render() }}
        </div>
        <div>
            {{ \Filament\Widgets\FilamentInfoWidget::make(['columnSpan' => 1])->render() }}
        </div>
    </div>
</x-filament-panels::page>

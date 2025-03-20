<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\CurrentBalanceWidget;
use App\Filament\Widgets\TransactionStatsWidget;
use App\Filament\Widgets\FuelTypeDonutWidget;
use App\Filament\Widgets\LatestBalancesWidget;
use App\Filament\Widgets\BalanceStatsWidget;
use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\TopVehiclesWidget;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    /**
     * Get the widgets that should be rendered in the header of the dashboard.
     *
     * @return array
     */
    public function getHeaderWidgets(): array
    {
        return [
            // 1. Current balance and last deposit cards
            // CurrentBalanceWidget::class,

            // 2-3. Balance Stats and Latest Balances side by side
            BalanceStatsWidget::class,
            LatestBalancesWidget::class,

            // 4-5. Transaction Statistics and Fuel Type Donut side by side
            TransactionStatsWidget::class,
            FuelTypeDonutWidget::class,
        ];
    }

    /**
     * Get the widgets displayed in the main content area of the dashboard.
     *
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            // 6-7. Latest Transactions and Top Vehicles side by side
            LatestTransactionsWidget::class,
            TopVehiclesWidget::class,

            // Admin widgets at the bottom
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }
}

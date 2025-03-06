<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use App\Filament\Resources\BalanceResource\Widgets\BalanceStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalances extends ListRecords
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BalanceStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getWidgets(): array
    {
        return [
            BalanceStatsWidget::class,
        ];
    }
}

<?php

namespace App\Filament\Resources\PettyCashDepositResource\Pages;

use App\Filament\Resources\PettyCashDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPettyCashDeposits extends ListRecords
{
    protected static string $resource = PettyCashDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PettyCashDepositResource\Pages;

use App\Filament\Resources\PettyCashDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPettyCashDeposit extends EditRecord
{
    protected static string $resource = PettyCashDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

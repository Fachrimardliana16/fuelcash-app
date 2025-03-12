<?php

namespace App\Filament\Resources\PettyCashExpenseResource\Pages;

use App\Filament\Resources\PettyCashExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPettyCashExpense extends EditRecord
{
    protected static string $resource = PettyCashExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PettyCashExpenseAllocationResource\Pages;

use App\Filament\Resources\PettyCashExpenseAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPettyCashExpenseAllocation extends EditRecord
{
    protected static string $resource = PettyCashExpenseAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

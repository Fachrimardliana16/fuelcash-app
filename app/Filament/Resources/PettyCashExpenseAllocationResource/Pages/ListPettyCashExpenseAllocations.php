<?php

namespace App\Filament\Resources\PettyCashExpenseAllocationResource\Pages;

use App\Filament\Resources\PettyCashExpenseAllocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPettyCashExpenseAllocations extends ListRecords
{
    protected static string $resource = PettyCashExpenseAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

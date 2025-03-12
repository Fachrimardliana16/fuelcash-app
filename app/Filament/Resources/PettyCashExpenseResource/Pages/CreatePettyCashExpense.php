<?php

namespace App\Filament\Resources\PettyCashExpenseResource\Pages;

use App\Filament\Resources\PettyCashExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePettyCashExpense extends CreateRecord
{
    protected static string $resource = PettyCashExpenseResource::class;
}

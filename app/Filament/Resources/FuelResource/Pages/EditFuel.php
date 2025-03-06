<?php

namespace App\Filament\Resources\FuelResource\Pages;

use App\Filament\Resources\FuelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFuel extends EditRecord
{
    protected static string $resource = FuelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

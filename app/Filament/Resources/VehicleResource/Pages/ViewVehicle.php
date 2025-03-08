<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit'),
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Kendaraan')
                ->modalDescription('Apakah Anda yakin ingin menghapus data kendaraan ini?')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}

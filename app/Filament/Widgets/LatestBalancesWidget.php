<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BalanceResource;
use App\Models\Balance;
use App\Models\FuelType;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBalancesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Riwayat Saldo Kas BBM';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Balance::query()->latest('date')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis BBM')
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Jumlah Deposit')
                    ->money('idr')
                    ->sortable()
                    ->color('success')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Saldo')
                    ->money('idr')
                    ->sortable()
                    ->alignRight()
                    ->color(
                        fn(Balance $balance) =>
                        $balance->remaining_balance < 1000000 ? 'danger' : 'success'
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Balance $record): string => BalanceResource::getUrl('view', ['record' => $record]))
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->button(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->url(fn(Balance $record): string => BalanceResource::getUrl('edit', ['record' => $record]))
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-m-pencil-square'),
                ])
                    ->dropdown()
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('primary')
                    ->button(),
            ])
            ->defaultSort('date', 'desc')
            ->heading('Riwayat Saldo Kas BBM')
            ->filters([
                Tables\Filters\SelectFilter::make('fuel_type_id')
                    ->label('Jenis BBM')
                    ->options(FuelType::pluck('name', 'id')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view_all')
                    ->label('Lihat Semua')
                    ->url(BalanceResource::getUrl())
                    ->color('primary')
                    ->icon('heroicon-m-arrow-right')
                    ->button(),
            ]);
    }

    // Explicitly define the record key method
    public function getTableRecordKey(mixed $record): string
    {
        return (string) $record->id;
    }
}

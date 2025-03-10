<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
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
                Balance::query()->latest('date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Jumlah Deposit')
                    ->money('Rp. ')
                    ->sortable()
                    ->color('success')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Saldo')
                    ->money('Rp. ')
                    ->sortable()
                    ->alignRight()
                    ->color(
                        fn(Balance $balance) =>
                        $balance->remaining_balance < 1000000 ? 'danger' : 'success'
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc');
    }

    // Explicitly define the record key method
    public function getTableRecordKey(mixed $record): string
    {
        return (string) $record->id;
    }
}

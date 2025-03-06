<?php

namespace App\Filament\Resources\BalanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Transactions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Vehicle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable(),

                Tables\Columns\TextColumn::make('usage_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('idr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_description')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\ImageColumn::make('fuel_receipt')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('usage_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('usage_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('usage_date', '<=', $date),
                            );
                    })
            ])
            ->defaultSort('usage_date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->heading('Transaction History');
    }
}

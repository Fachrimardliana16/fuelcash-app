<?php

namespace App\Filament\Resources\BalanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Transaksi BBM';
    protected static ?string $label = 'Transaksi';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.vehicleType.name')
                    ->label('Jenis Kendaraan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('owner')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis BBM')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fuel.name')
                    ->label('BBM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('idr')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('usage_description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('fuel_receipt')
                    ->label('Struk BBM')
                    ->toggleable()
                    ->circular(),

                Tables\Columns\ImageColumn::make('invoice')
                    ->label('Form Permintaan')
                    ->toggleable()
                    ->circular(),
            ])
            ->defaultSort('usage_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type_id')
                    ->relationship('vehicle.vehicleType', 'name')
                    ->label('Jenis Kendaraan')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('fuel_type_id')
                    ->relationship('fuelType', 'name')
                    ->label('Jenis BBM')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('usage_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('usage_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('usage_date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->url(fn($record) => route('filament.admin.resources.transactions.view', $record)),
            ])
            ->heading('Riwayat Transaksi')
            ->paginated([10, 25, 50, 100])
            ->poll('60s');
    }
}

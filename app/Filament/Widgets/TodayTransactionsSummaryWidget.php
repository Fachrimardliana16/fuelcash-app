<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Fuel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class TodayTransactionsSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Transaksi Hari Ini';
    protected static ?string $pollingInterval = '15s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->whereDate('usage_date', Carbon::today())
                    ->latest('usage_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Kendaraan')
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fuel.name')
                    ->label('BBM')
                    ->badge()
                    ->color(
                        fn(Transaction $record) =>
                        match ($record->fuel?->name) {
                            'Pertalite' => 'success',
                            'Pertamax' => 'warning',
                            'Solar' => 'danger',
                            default => 'gray',
                        }
                    ),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR', true)
                    ->sortable()
                    ->alignRight()
                    ->weight('bold'),
            ])
            ->defaultSort('usage_date', 'desc')
            ->emptyStateHeading('Belum Ada Transaksi Hari Ini')
            ->emptyStateDescription('Transaksi akan muncul di sini saat terjadi')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }

    // Explicitly define the record key method
    public function getTableRecordKey(mixed $record): string
    {
        return (string) $record->id;
    }
}

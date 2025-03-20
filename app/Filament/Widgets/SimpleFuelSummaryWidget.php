<?php

namespace App\Filament\Widgets;

use App\Models\Fuel;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SimpleFuelSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Analisa BBM Bulan Ini';
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        return $table
            ->query(
                Fuel::query()
                    ->select([
                        'fuels.id',
                        'fuels.name',
                        DB::raw('COUNT(transactions.id) as transaction_count'),
                        DB::raw('SUM(transactions.amount) as total_amount'),
                        DB::raw('AVG(transactions.amount) as average_amount')
                    ])
                    ->leftJoin('transactions', 'fuels.id', '=', 'transactions.fuel_id')
                    ->whereMonth('transactions.usage_date', $currentMonth)
                    ->whereYear('transactions.usage_date', $currentYear)
                    ->groupBy('fuels.id', 'fuels.name')
                    ->orderByDesc('total_amount')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('BBM')
                    ->searchable()
                    ->badge()
                    ->color(fn(Fuel $record): string => match ($record->name) {
                        'Pertalite' => 'success',
                        'Pertamax' => 'warning',
                        'Solar' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('transaction_count')
                    ->label('Jml')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (Rp)')
                    ->money('IDR', true)
                    ->sortable()
                    ->alignRight()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('average_amount')
                    ->label('Rata²')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state / 1000, 0) . 'K')
                    ->sortable()
                    ->alignRight(),
            ])
            ->defaultSort('total_amount', 'desc')
            ->poll('60s')
            ->striped();
    }

    public function getTableRecordKey(mixed $record): string
    {
        return (string) $record->id;
    }
}

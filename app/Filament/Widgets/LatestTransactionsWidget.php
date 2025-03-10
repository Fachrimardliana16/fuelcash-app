<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactionsWidget extends BaseWidget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Riwayat Transaksi';
    protected static ?int $limit = 10;

    public ?string $filter = 'all';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->when($this->filter === 'today', fn($query) => $query->whereDate('usage_date', now()))
                    ->when($this->filter === 'week', fn($query) => $query->where('usage_date', '>=', now()->startOfWeek()))
                    ->when($this->filter === 'month', fn($query) => $query->where('usage_date', '>=', now()->startOfMonth()))
                    ->latest('usage_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('owner')
                    ->label('Pemilik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fuel.name')
                    ->label('Bahan Bakar')
                    ->searchable()
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
                    ->money('IDR')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('usage_description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fuel_id')
                    ->relationship('fuel', 'name')
                    ->label('Fuel Type'),
                Tables\Filters\SelectFilter::make('vehicle_type_id')
                    ->relationship('vehicleType', 'name')
                    ->label('Vehicle Type'),
            ])
            ->defaultSort('usage_date', 'desc');
    }

    // Explicitly define the record key method
    public function getTableRecordKey(mixed $record): string
    {
        return (string) $record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Forms\Components\Select::make('filter')
                ->options([
                    'all' => 'All Transactions',
                    'today' => 'Today Only',
                    'week' => 'This Week',
                    'month' => 'This Month',
                ])
                ->default('all')
                ->live()
                ->afterStateUpdated(fn() => $this->refresh()),
        ];
    }
}

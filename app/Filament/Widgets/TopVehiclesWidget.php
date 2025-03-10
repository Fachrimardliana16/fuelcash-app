<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\VehicleType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopVehiclesWidget extends BaseWidget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'half';
    protected static ?string $heading = 'Daftar Kendaraan Teratas';

    // Filter properties
    public ?string $period = 'all';
    public ?string $vehicleType = 'all';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    // Apply time period filter
                    ->when(
                        $this->period === 'month',
                        fn($query) =>
                        $query->where('usage_date', '>=', Carbon::now()->startOfMonth())
                    )
                    ->when(
                        $this->period === 'quarter',
                        fn($query) =>
                        $query->where('usage_date', '>=', Carbon::now()->startOfQuarter())
                    )
                    ->when(
                        $this->period === 'year',
                        fn($query) =>
                        $query->where('usage_date', '>=', Carbon::now()->startOfYear())
                    )
                    // Apply vehicle type filter
                    ->when(
                        $this->vehicleType !== 'all',
                        fn($query) =>
                        $query->where('vehicle_type_id', $this->vehicleType)
                    )
                    ->select(
                        'license_plate',
                        'owner',
                        'vehicles_id',
                        'vehicle_type_id',
                        DB::raw('COUNT(*) as transaction_count'),
                        DB::raw('SUM(amount) as total_amount'),
                        DB::raw('AVG(amount) as average_transaction')
                    )
                    ->groupBy('license_plate', 'owner', 'vehicles_id', 'vehicle_type_id')
                    ->orderByDesc('transaction_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('owner')
                    ->label('Pemilik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_count')
                    ->label('Jumlah Transaksi')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->label('Jumlah Rupiah')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('average_transaction')
                    ->money('IDR')
                    ->label('Rata-rata')
                    ->sortable()
                    ->alignRight(),
            ])
            ->defaultSort('transaction_count', 'desc')
            ->striped();
    }

    // Generate a unique key for each record
    public function getTableRecordKey(mixed $record): string
    {
        return spl_object_hash($record);
    }

    protected function getHeaderActions(): array
    {
        $vehicleTypes = VehicleType::pluck('name', 'id')->toArray();

        return [
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Select::make('period')
                        ->options([
                            'all' => 'All Time',
                            'month' => 'This Month',
                            'quarter' => 'This Quarter',
                            'year' => 'This Year',
                        ])
                        ->default('all')
                        ->live()
                        ->afterStateUpdated(fn() => $this->refresh()),

                    Forms\Components\Select::make('vehicleType')
                        ->options(array_merge(['all' => 'All Vehicle Types'], $vehicleTypes))
                        ->default('all')
                        ->live()
                        ->afterStateUpdated(fn() => $this->refresh()),
                ])->columns(2),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use App\Models\CompanySetting;
use App\Models\FuelType;
use App\Models\Signature;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Saldo Bahan Bakar';
    protected static ?string $navigationGroup = 'Manajemen Kas BBM';
    protected static ?int $navigationSort = 1;
    protected static ?int $navigationGroupSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Forms\Components\Section::make('Informasi Deposit')
                    ->description('Masukkan detail deposit')
                    ->schema([
                        Forms\Components\Select::make('fuel_type_id')
                            ->label('Jenis BBM')
                            ->options(FuelType::pluck('name', 'id'))
                            ->required()
                            ->placeholder('Pilih jenis BBM')
                            ->live()
                            ->validationMessages([
                                'required' => 'Silakan pilih jenis BBM',
                            ]),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Input')
                            ->required()
                            ->default(now())
                            ->placeholder('Pilih tanggal')
                            ->validationMessages([
                                'required' => 'Silakan pilih tanggal',
                            ]),

                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Jumlah Deposit')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('numeric')
                            ->placeholder('Masukkan jumlah deposit')
                            ->live(onBlur: true, debounce: 500)
                            ->minValue(1)
                            ->validationMessages([
                                'required' => 'Silakan masukkan jumlah deposit',
                                'numeric' => 'Jumlah harus berupa angka',
                                'min' => 'Jumlah harus lebih besar dari 0',
                            ])
                            ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                $fuelTypeId = $get('fuel_type_id');
                                if (!$fuelTypeId) return;

                                $lastBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                    ->latest()
                                    ->first()?->remaining_balance ?? 0;

                                $newBalance = $lastBalance + (float)($state ?? 0);
                                $set('remaining_balance', $newBalance);

                                // Validasi jumlah deposit terhadap maksimal yang bisa ditambahkan
                                $fuelType = FuelType::find($fuelTypeId);
                                if ($fuelType) {
                                    $maxDeposit = $fuelType->max_deposit;
                                    $remainingCapacity = max(0, $maxDeposit - $lastBalance);

                                    if ((float)$state > $remainingCapacity) {
                                        Notification::make()
                                            ->warning()
                                            ->title('Peringatan Deposit')
                                            ->body('Jumlah deposit melebihi batas maksimal yang diizinkan!')
                                            ->persistent()
                                            ->send();
                                    }
                                }
                            }),

                        Forms\Components\Section::make('Informasi Batas Deposit')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Placeholder::make('max_deposit_info')
                                    ->label('1. Maksimal Deposit')
                                    ->content(function ($get) {
                                        $fuelTypeId = $get('fuel_type_id');
                                        if (!$fuelTypeId) return 'Pilih jenis BBM terlebih dahulu';

                                        $fuelType = FuelType::find($fuelTypeId);
                                        return 'Rp ' . number_format($fuelType?->max_deposit ?? 0, 0, ',', '.');
                                    }),

                                Forms\Components\Placeholder::make('remaining_deposit_capacity')
                                    ->label('2. Sisa Deposit')
                                    ->content(function ($get) {
                                        $fuelTypeId = $get('fuel_type_id');
                                        if (!$fuelTypeId) return 'Pilih jenis BBM terlebih dahulu';

                                        $fuelType = FuelType::find($fuelTypeId);
                                        $maxDeposit = $fuelType?->max_deposit ?? 0;

                                        $lastBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                            ->latest()
                                            ->first()?->remaining_balance ?? 0;

                                        $remainingCapacity = max(0, $maxDeposit - $lastBalance);
                                        return 'Rp ' . number_format($remainingCapacity, 0, ',', '.');
                                    }),

                                Forms\Components\Placeholder::make('addable_balance_status')
                                    ->label('3. Saldo yang Bisa Ditambahkan')
                                    ->content(function ($get) {
                                        $fuelTypeId = $get('fuel_type_id');
                                        if (!$fuelTypeId) return 'Pilih jenis BBM terlebih dahulu';

                                        $depositAmount = (float)($get('deposit_amount') ?? 0);
                                        if ($depositAmount <= 0) return 'Masukkan jumlah deposit';

                                        $fuelType = FuelType::find($fuelTypeId);
                                        $maxDeposit = $fuelType?->max_deposit ?? 0;

                                        $lastBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                            ->latest()
                                            ->first()?->remaining_balance ?? 0;

                                        // Hitung saldo yang bisa ditambahkan (maksimal deposit - sisa deposit saat ini)
                                        $addableBalance = max(0, $maxDeposit - $lastBalance);
                                        $result = 'Rp ' . number_format($addableBalance, 0, ',', '.');

                                        // Cek apakah jumlah deposit melebihi yang diizinkan
                                        if ($depositAmount > $addableBalance) {
                                            return '❌ ' . $result . ' (Deposit melebihi batas maksimal!)';
                                        }

                                        return '✅ ' . $result;
                                    }),
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Saldo')
                    ->schema([
                        Forms\Components\TextInput::make('remaining_balance')
                            ->label('Sisa Saldo')
                            ->required()
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->placeholder('Dihitung secara otomatis'),

                        Forms\Components\Placeholder::make('last_balance')
                            ->label('Saldo Terakhir')
                            ->content(function ($get) {
                                $fuelTypeId = $get('fuel_type_id');
                                if (!$fuelTypeId) return 'Pilih jenis BBM terlebih dahulu';

                                $lastBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                    ->latest()
                                    ->first()?->remaining_balance ?? 0;

                                return 'Rp ' . number_format($lastBalance, 0, ',', '.');
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis BBM')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Jumlah Deposit')
                    ->money('idr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Saldo')
                    ->money('idr')
                    ->sortable()
                    ->color(fn(Balance $record): string => $record->remaining_balance > 1000000 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('generateBalanceReport')
                    ->label('Buat Laporan')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Select::make('fuel_type_id')
                            ->label('Jenis BBM')
                            ->options(FuelType::pluck('name', 'id'))
                            ->placeholder('Semua Jenis BBM'),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now()->startOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $query = Balance::with(['user', 'fuelType'])
                            ->whereBetween('date', [$data['start_date'], $data['end_date']]);

                        if (!empty($data['fuel_type_id'])) {
                            $query->where('fuel_type_id', $data['fuel_type_id']);
                        }

                        $balances = $query->orderBy('date', 'desc')->get();
                        $company = CompanySetting::first();

                        // Group balances by fuel type
                        $balancesByFuelType = $balances->groupBy('fuel_type_id');
                        $totals = [];

                        foreach ($balancesByFuelType as $fuelTypeId => $fuelBalances) {
                            $fuelType = FuelType::find($fuelTypeId);
                            $totals[$fuelTypeId] = [
                                'name' => $fuelType->name,
                                'total_deposit' => $fuelBalances->sum('deposit_amount'),
                                'current_balance' => $fuelBalances->sortByDesc('created_at')->first()->remaining_balance
                            ];
                        }

                        $pdf = Pdf::loadView('pdf.balance-report', [
                            'balancesByFuelType' => $balancesByFuelType,
                            'totals' => $totals,
                            'company' => $company,
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date']
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'laporan-saldo-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
                Tables\Actions\Action::make('generateBalanceSummaryReport')
                    ->label('Laporan Rekapitulasi')
                    ->color('success')
                    ->icon('heroicon-o-document-chart-bar')
                    ->form([
                        Forms\Components\Select::make('fuel_type_id')
                            ->label('Jenis BBM')
                            ->options(FuelType::pluck('name', 'id'))
                            ->placeholder('Semua Jenis BBM'),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now()->startOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = Carbon::parse($data['end_date']);

                        // Get fuel types based on selection
                        $fuelTypes = empty($data['fuel_type_id'])
                            ? FuelType::all()
                            : FuelType::where('id', $data['fuel_type_id'])->get();

                        $summaries = [];

                        foreach ($fuelTypes as $fuelType) {
                            // Get initial balance (last balance before start date)
                            $initialBalance = Balance::where('fuel_type_id', $fuelType->id)
                                ->where('date', '<', $startDate)
                                ->latest()
                                ->first()?->remaining_balance ?? 0;

                            // Get deposits during period
                            $deposits = Balance::where('fuel_type_id', $fuelType->id)
                                ->whereBetween('date', [$startDate, $endDate])
                                ->sum('deposit_amount');

                            // Get usage during period
                            $usage = Transaction::where('fuel_type_id', $fuelType->id)
                                ->whereBetween('usage_date', [$startDate, $endDate])
                                ->sum('amount');

                            // Calculate totals
                            $totalAmount = $initialBalance + $deposits;
                            $currentBalance = $totalAmount - $usage;

                            $summaries[$fuelType->id] = [
                                'fuel_type_name' => $fuelType->name,
                                'initial_balance' => $initialBalance,
                                'deposit' => $deposits,
                                'total_amount' => $totalAmount,
                                'usage' => $usage,
                                'current_balance' => $currentBalance
                            ];
                        }

                        $company = CompanySetting::first();

                        $pdf = Pdf::loadView('pdf.balance-summary-report', [
                            'summaries' => $summaries,
                            'company' => $company,
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'selectedFuelTypeId' => $data['fuel_type_id'] ?? null,
                        ]);

                        $filename = empty($data['fuel_type_id'])
                            ? 'rekap-semua-bbm-'
                            : 'rekap-bbm-' . Str::slug($fuelTypes->first()->name) . '-';

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->button()
                    ->color('info')
                    ->icon('heroicon-m-eye'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->color('danger')
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Transaksi')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data transaksi ini?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->before(function (Tables\Actions\DeleteAction $action) {
                            if ($action->getRecord()->balance_id) {
                                Notification::make()
                                    ->danger()
                                    ->title('Transaksi tidak dapat dihapus')
                                    ->body('Transaksi ini terkait dengan saldo')
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ])
                    ->dropdown()
                    ->button()
                    ->color('primary')
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'view' => Pages\ViewBalance::route('/{record}'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}

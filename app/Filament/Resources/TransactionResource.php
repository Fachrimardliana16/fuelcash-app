<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Balance;
use App\Models\CompanySetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;  // Add this import

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Manajemen Kas BBM';
    protected static ?int $navigationSort = 2;
    protected static ?int $navigationGroupSort = 3; // Changed from ?string to ?int
    protected static ?string $navigationLabel = 'Transaksi BBM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Forms\Components\Section::make('Data Kendaraan')
                    ->description('Informasi kendaraan yang digunakan')
                    ->schema([
                        Forms\Components\Select::make('vehicles_id')
                            ->relationship('vehicle', 'license_plate', fn($query) => $query->where('isactive', true))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->license_plate} - {$record->owner}")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Nomor Kendaraan')
                            ->placeholder('Pilih Nomor Kendaraan kendaraan')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $vehicle = \App\Models\Vehicle::with('vehicleType')->find($state);
                                    if ($vehicle) {
                                        $set('license_plate', $vehicle->license_plate);
                                        $set('vehicle_type_id', $vehicle->vehicle_type_id);
                                        $set('vehicle_type_name', $vehicle->vehicleType->name);
                                        $set('owner', $vehicle->owner);
                                    }
                                } else {
                                    $set('license_plate', null);
                                    $set('vehicle_type_id', null);
                                    $set('vehicle_type_name', null);
                                    $set('owner', null);
                                }
                            }),

                        Forms\Components\TextInput::make('vehicle_type_name')
                            ->label('Jenis Kendaraan')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('owner')
                            ->label('Pemilik')
                            ->required()
                            ->dehydrated(true), // Changed to true to ensure it's saved

                        Forms\Components\Hidden::make('vehicle_type_id'),
                        Forms\Components\Hidden::make('license_plate'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Penggunaan BBM')
                    ->description('Informasi penggunaan bahan bakar')
                    ->schema([
                        Forms\Components\DatePicker::make('usage_date')
                            ->required()
                            ->default(now())
                            ->label('Tanggal Penggunaan')
                            ->placeholder('Pilih tanggal')
                            ->rules(['required', 'date'])
                            ->validationMessages([
                                'required' => 'Tanggal penggunaan wajib diisi',
                                'date' => 'Format tanggal tidak valid',
                            ]),

                        Forms\Components\Select::make('fuel_type_id')
                            ->relationship('fuelType', 'name', fn($query) => $query->where('isactive', true))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Jenis BBM')
                            ->placeholder('Pilih jenis BBM')
                            ->rules(['required'])
                            ->validationMessages([
                                'required' => 'Jenis BBM wajib dipilih',
                            ]),

                        Forms\Components\Select::make('fuel_id')
                            ->options(function (Forms\Get $get) {
                                $fuelTypeId = $get('fuel_type_id');
                                if (!$fuelTypeId) return [];
                                return \App\Models\Fuel::where('fuel_type_id', $fuelTypeId)
                                    ->where('isactive', true)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->label('BBM')
                            ->placeholder('Pilih BBM')
                            ->disabled(fn(Forms\Get $get) => !$get('fuel_type_id'))
                            ->rules(['required'])
                            ->validationMessages([
                                'required' => 'BBM wajib dipilih',
                            ]),

                        Forms\Components\Placeholder::make('available_balance')
                            ->label('Saldo Tersedia')
                            ->content(function (Forms\Get $get) {
                                $fuelTypeId = $get('fuel_type_id');
                                if (!$fuelTypeId) {
                                    return "Pilih jenis BBM terlebih dahulu";
                                }

                                $latestBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                    ->latest()
                                    ->first();

                                $availableBalance = $latestBalance ? $latestBalance->remaining_balance : 0;
                                return "Rp " . number_format($availableBalance, 0, ',', '.');
                            }),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Jumlah')
                            ->placeholder('Masukkan jumlah')
                            ->mask('999999999')
                            ->live(onBlur: true, debounce: 500) // Corrected live implementation with both onBlur and debounce
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $fuelId = $get('fuel_id');
                                if ($fuelId && $state) {
                                    $fuel = \App\Models\Fuel::find($fuelId);
                                    if ($fuel && $fuel->price > 0) {
                                        $volume = $state / $fuel->price;
                                        $set('volume', number_format($volume, 2));
                                    }
                                }

                                // Add validation for balance
                                $fuelTypeId = $get('fuel_type_id');
                                if ($fuelTypeId && $state) {
                                    $latestBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                        ->latest()
                                        ->first();

                                    $availableBalance = $latestBalance ? $latestBalance->remaining_balance : 0;

                                    if ($state > $availableBalance) {
                                        $set('balance_warning', "Jumlah melebihi saldo tersedia (Rp " . number_format($availableBalance, 0, ',', '.') . ")");
                                    } else {
                                        $set('balance_warning', null);
                                    }
                                }
                            })
                            ->rules([
                                'required',
                                'numeric',
                                'min:1',
                                function (Forms\Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $fuelTypeId = $get('fuel_type_id');
                                        if (!$fuelTypeId) return;

                                        $latestBalance = Balance::where('fuel_type_id', $fuelTypeId)
                                            ->latest()
                                            ->first();

                                        $availableBalance = $latestBalance ? $latestBalance->remaining_balance : 0;

                                        if ($value > $availableBalance) {
                                            $fail("Saldo tidak mencukupi. Saldo tersedia: Rp " . number_format($availableBalance, 0, ',', '.'));
                                        }
                                    };
                                }
                            ])
                            ->validationMessages([
                                'required' => 'Jumlah wajib diisi',
                                'numeric' => 'Jumlah harus berupa angka',
                                'min' => 'Jumlah minimal 1',
                            ]),

                        Forms\Components\Placeholder::make('balance_warning')
                            ->label('')
                            ->content(fn(Forms\Get $get) => $get('balance_warning'))
                            ->extraAttributes([
                                'class' => 'text-danger-500 font-medium',
                            ])
                            ->visible(fn(Forms\Get $get) => $get('balance_warning')),

                        Forms\Components\TextInput::make('volume')
                            ->label('Volume BBM')
                            ->suffix('Liter')
                            ->disabled()
                            ->dehydrated(true),

                    ])->columns(2),

                Forms\Components\Section::make('Keterangan & Dokumen')
                    ->description('Informasi tambahan dan dokumen pendukung')
                    ->schema([
                        Forms\Components\Textarea::make('usage_description')
                            ->required()
                            ->label('Keterangan Penggunaan')
                            ->placeholder('Masukkan keterangan penggunaan BBM')
                            ->rules(['required', 'min:10'])
                            ->validationMessages([
                                'required' => 'Keterangan wajib diisi',
                                'min' => 'Keterangan minimal 10 karakter',
                            ])
                            ->columnSpanFull(),

                        FileUpload::make('fuel_receipt')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->imageEditorViewportWidth('1920')
                            ->imageEditorViewportHeight('1080')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'image.heic',
                                'image.heif'
                            ])
                            ->maxSize(10240)
                            ->directory('fuel-receipts')
                            ->optimize('jpg')
                            ->label('Struk BBM')
                            ->columnSpanFull()
                            ->validationMessages([
                                'image' => 'File harus berupa gambar',
                                'max' => 'Ukuran file maksimal 10MB',
                            ]),

                        FileUpload::make('invoice')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->imageEditorViewportWidth('1920')
                            ->imageEditorViewportHeight('1080')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image.png',
                                'image/jpg',
                                'image.heic',
                                'image.heif'
                            ])
                            ->maxSize(10240)
                            ->directory('invoices')
                            ->optimize('jpg')
                            ->label('Form Permintaan BBM')
                            ->columnSpanFull()
                            ->validationMessages([
                                'image' => 'File harus berupa gambar',
                                'max' => 'Ukuran file maksimal 10MB',
                            ]),

                        Forms\Components\Select::make('balance_id')
                            ->relationship('balance', 'id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn() => Balance::latest()->first()?->id)
                            ->label('Saldo')
                            ->hidden(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Detail Kendaraan')
                    ->formatStateUsing(function ($record) {
                        $owner = $record->owner ?? '-';
                        $plate = $record->license_plate ?? '-';

                        return "{$owner}<br>
                                {$plate}";
                    })
                    ->html()
                    ->searchable(['owner', 'license_plate'])
                    ->sortable()
                    ->wrap()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis BBM & BBM')
                    ->formatStateUsing(function ($record) {
                        $fuelType = $record->fuelType->name ?? '-';
                        $fuelName = $record->fuel->name ?? '-';

                        return "{$fuelType}<br>
                                {$fuelName}";
                    })
                    ->html()
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah & Volume')
                    ->formatStateUsing(function ($record) {
                        $amount = "Rp " . number_format($record->amount, 0, ',', '.');
                        $volume = number_format($record->volume, 2, ',', '.') . " Liter";

                        return "{$amount}<br>
                                {$volume}";
                    })
                    ->html()
                    ->sortable()
                    ->wrap()

                    ->color('success'),

                Tables\Columns\TextColumn::make('usage_description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('fuel_receipt')
                    ->label('Struk BBM')
                    ->circular()
                    ->size(40),

                Tables\Columns\ImageColumn::make('invoice')
                    ->label('Form Permintaan')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->headerActions([
                Tables\Actions\Action::make('generateTransactionReport')
                    ->label('Buat Laporan')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Select::make('vehicle_type_id')
                            ->label('Jenis Kendaraan')
                            ->relationship('vehicleType', 'name')
                            ->placeholder('Semua Jenis Kendaraan')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('fuel_type_id')
                            ->label('Jenis BBM')
                            ->relationship('fuelType', 'name')
                            ->placeholder('Semua Jenis BBM')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('vehicles_id')
                            ->label('Nomor Kendaraan')
                            ->relationship(
                                'vehicle',
                                'license_plate',
                                fn ($query) => $query->select(['id', 'license_plate', 'owner'])
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->license_plate} - {$record->owner}")
                            ->placeholder('Semua Nomor Kendaraan')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->required()
                            ->default(now()->startOfMonth()),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->default(now())
                            ->afterOrEqual('start_date'),
                    ])
                    ->action(function (array $data) {
                        $query = Transaction::with(['vehicle.vehicleType', 'fuelType', 'balance', 'user'])
                            ->whereBetween('usage_date', [
                                $data['start_date'],
                                $data['end_date']
                            ]);

                        // Apply filters if selected
                        if (!empty($data['vehicle_type_id'])) {
                            $query->where('vehicle_type_id', $data['vehicle_type_id']);
                        }

                        if (!empty($data['fuel_type_id'])) {
                            $query->where('fuel_type_id', $data['fuel_type_id']);
                        }

                        if (!empty($data['vehicles_id'])) {
                            $query->where('vehicles_id', $data['vehicles_id']);
                        }

                        $transactions = $query->orderBy('usage_date', 'asc')
                            ->orderBy('created_at', 'asc')
                            ->get();

                        if ($transactions->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak ada transaksi')
                                ->body('Tidak ada transaksi dalam rentang tanggal yang dipilih')
                                ->send();
                            return;
                        }

                        $dateRange = Carbon::parse($data['start_date'])->format('d/m/Y') . ' - ' .
                                   Carbon::parse($data['end_date'])->format('d/m/Y');

                        $company = CompanySetting::first();

                        $vehicleType = null;
                        $fuelType = null;
                        $vehiclePlate = null;

                        if (!empty($data['vehicle_type_id'])) {
                            $vehicleType = \App\Models\VehicleType::find($data['vehicle_type_id'])->name;
                        }

                        if (!empty($data['fuel_type_id'])) {
                            $fuelType = \App\Models\FuelType::find($data['fuel_type_id'])->name;
                        }

                        if (!empty($data['vehicles_id'])) {
                            $vehiclePlate = \App\Models\Vehicle::find($data['vehicles_id'])->license_plate;
                        }

                        // Group transactions by fuel type and calculate totals
                        $transactionsByFuelType = $transactions->groupBy('fuelType.name');
                        $totals = [];

                        foreach ($transactionsByFuelType as $fuelTypeName => $fuelTypeTransactions) {
                            $initialBalance = $fuelTypeTransactions->first()->balance->remaining_balance + $fuelTypeTransactions->sum('amount');
                            $totals[$fuelTypeName] = [
                                'initial_balance' => $initialBalance,
                                'total_amount' => $fuelTypeTransactions->sum('amount'),
                                'total_volume' => $fuelTypeTransactions->sum('volume'),
                                'remaining_balance' => $initialBalance - $fuelTypeTransactions->sum('amount')
                            ];
                        }

                        $pdf = Pdf::loadView('reports.transactions', [
                            'transactionsByFuelType' => $transactionsByFuelType,
                            'totals' => $totals,
                            'dateRange' => $dateRange,
                            'company' => $company,
                            'vehicleType' => $vehicleType,
                            'fuelType' => $fuelType,
                            'vehiclePlate' => $vehiclePlate,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'laporan-transaksi-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make(),
                ])->label('Aksi Grup'),
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TransactionResource\Widgets\TransactionStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Balance;
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

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Manajemen BBM';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Transaksi BBM';

    public static function form(Form $form): Form
    {
        $latestBalance = Balance::latest()->first();
        $availableBalance = $latestBalance ? $latestBalance->remaining_balance : 0;

        return $form
            ->schema([
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
                            ->label('Plat Nomor')
                            ->placeholder('Pilih plat nomor kendaraan')
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
                            ->relationship('fuelType', 'name')
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
                            ->content("Rp " . number_format($availableBalance, 0, ',', '.')),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Jumlah')
                            ->placeholder('Masukkan jumlah')
                            ->mask('999999999')
                            ->live()
                            ->rules([
                                'required',
                                'numeric',
                                'min:1',
                                'max:' . $availableBalance
                            ])
                            ->validationMessages([
                                'required' => 'Jumlah wajib diisi',
                                'numeric' => 'Jumlah harus berupa angka',
                                'min' => 'Jumlah minimal 1',
                                'max' => "Saldo tidak mencukupi. Saldo tersedia: Rp " . number_format($availableBalance, 0, ',', '.')
                            ])
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

                        Forms\Components\FileUpload::make('fuel_receipt')
                            ->image()
                            ->maxSize(5120) // 5MB allowed for upload
                            ->directory('fuel-receipts')
                            ->label('Struk BBM')
                            ->columnSpanFull()
                            ->rules(['image'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->validationMessages([
                                'image' => 'File harus berupa gambar',
                            ])
                            // Handle image upload and compression
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                                $filename = $file->hashName();

                                // Generate path where the file will be stored
                                $directory = 'fuel-receipts';
                                $path = $directory . '/' . $filename;

                                // Create temporary disk path for compression
                                $tempPath = $file->getRealPath();

                                // Using Intervention Image v3 syntax
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($tempPath);

                                // Resize if larger than 1200px on any dimension
                                $width = $image->width();
                                $height = $image->height();

                                if ($width > 1200 || $height > 1200) {
                                    if ($width > $height) {
                                        $image->scale(width: 1200);
                                    } else {
                                        $image->scale(height: 1200);
                                    }
                                }

                                // Compress and encode as JPEG with 75% quality
                                $encodedImage = $image->toJpeg(75);

                                // Save to storage
                                Storage::disk('public')->put($path, $encodedImage);

                                return $path;
                            }),

                        Forms\Components\FileUpload::make('invoice')
                            ->image()
                            ->maxSize(5120) // 5MB allowed for upload
                            ->directory('invoices')
                            ->label('Nota/Kwitansi')
                            ->columnSpanFull()
                            ->rules(['image'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->validationMessages([
                                'image' => 'File harus berupa gambar',
                            ])
                            // Handle image upload and compression
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                                $filename = $file->hashName();

                                // Generate path where the file will be stored
                                $directory = 'invoices';
                                $path = $directory . '/' . $filename;

                                // Create temporary disk path for compression
                                $tempPath = $file->getRealPath();

                                // Using Intervention Image v3 syntax
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($tempPath);

                                // Resize if larger than 1200px on any dimension
                                $width = $image->width();
                                $height = $image->height();

                                if ($width > 1200 || $height > 1200) {
                                    if ($width > $height) {
                                        $image->scale(width: 1200);
                                    } else {
                                        $image->scale(height: 1200);
                                    }
                                }

                                // Compress and encode as JPEG with 75% quality
                                $encodedImage = $image->toJpeg(75);

                                // Save to storage
                                Storage::disk('public')->put($path, $encodedImage);

                                return $path;
                            }),

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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Plat Nomor')
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
                    ->label('Nota/Kwitansi')
                    ->toggleable()
                    ->circular(),

                Tables\Columns\TextColumn::make('balance.remaining_balance')
                    ->label('Sisa Saldo')
                    ->money('idr')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
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
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
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
                        $latestBalance = Balance::latest()->first();
                        $initialBalance = $latestBalance ? $latestBalance->remaining_balance : 0;

                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = \Carbon\Carbon::parse($data['end_date']);

                        $transactions = Transaction::with(['vehicle.vehicleType', 'fuelType', 'balance'])
                            ->whereBetween('usage_date', [
                                $startDate->startOfDay(),
                                $endDate->endOfDay()
                            ])
                            ->orderBy('usage_date', 'asc')
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

                        $dateRange = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');

                        $pdf = Pdf::loadView('reports.transactions', [
                            'transactions' => $transactions,
                            'dateRange' => $dateRange,
                            'initialBalance' => $initialBalance
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'laporan-transaksi-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make(),
                ])->label('Aksi Grup'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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

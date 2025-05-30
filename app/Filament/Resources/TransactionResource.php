<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Balance;
use App\Models\CompanySetting;
use App\Models\FuelType;  // Add this import
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

                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Grid::make(2)
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
                            ]),
                    ]),

                Forms\Components\Section::make('Data Kendaraan')
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('vehicles_id')
                                    ->relationship(
                                        'vehicle',
                                        'license_plate',
                                        function ($query, Forms\Get $get) {
                                            $fuelTypeId = $get('fuel_type_id');

                                            $query = $query->where('isactive', true);

                                            if ($fuelTypeId) {
                                                $query->where(function ($query) use ($fuelTypeId) {
                                                    $query->where('fuel_type_id', $fuelTypeId)
                                                        ->orWhereNull('fuel_type_id');
                                                });
                                            }

                                            return $query;
                                        }
                                    )
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->license_plate} - {$record->owner}")
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->label('Nomor Kendaraan')
                                    ->placeholder('Pilih Nomor Kendaraan kendaraan')
                                    ->disabled(fn(Forms\Get $get) => !$get('fuel_type_id'))
                                    ->helperText(fn(Forms\Get $get) => !$get('fuel_type_id') ? 'Pilih jenis BBM terlebih dahulu' : null)
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Informasi Kendaraan')
                                            ->description('Masukkan informasi detail kendaraan')
                                            ->schema([
                                                Forms\Components\Select::make('vehicle_type_id')
                                                    ->relationship('vehicleType', 'name')
                                                    ->label('Jenis Kendaraan')
                                                    ->required()
                                                    ->live()
                                                    ->validationMessages([
                                                        'required' => 'Jenis kendaraan harus dipilih',
                                                        'exists' => 'Jenis kendaraan tidak valid',
                                                    ])
                                                    ->rules(['required', 'exists:vehicle_types,id']),

                                                Forms\Components\TextInput::make('license_plate')
                                                    ->label('Nomor Kendaraan')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true)
                                                    ->regex('/^[A-Z]{1,2}\s*\d{1,4}\s*[A-Z]{1,3}$/')
                                                    ->validationMessages([
                                                        'required' => 'Nomor Kendaraan harus diisi',
                                                        'regex' => 'Format Nomor Kendaraan tidak valid (Contoh: B 1234 ABC)',
                                                        'unique' => 'Nomor Kendaraan sudah terdaftar',
                                                        'max' => 'Nomor Kendaraan maksimal 255 karakter',
                                                    ])
                                                    ->rules(['required', 'max:255', 'unique:vehicles,license_plate']),

                                                Forms\Components\Select::make('vehicle_model')
                                                    ->label('Tipe Kendaraan')
                                                    ->options([
                                                        'Pickup' => 'Pickup',
                                                        'Bebek' => 'Bebek',
                                                        'Matic' => 'Matic',
                                                        'SUV' => 'SUV',
                                                        'MPV' => 'MPV',
                                                        'Sport' => 'Sport',
                                                    ])
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Tipe kendaraan harus dipilih',
                                                        'in' => 'Tipe kendaraan tidak valid',
                                                    ])
                                                    ->rules(['required', 'in:Pickup,Bebek,Matic,SUV,MPV,Sport']),

                                                Forms\Components\Select::make('brand')
                                                    ->label('Merk')
                                                    ->options([
                                                        'Honda' => 'Honda',
                                                        'Toyota' => 'Toyota',
                                                        'Nissan' => 'Nissan',
                                                        'Suzuki' => 'Suzuki',
                                                        'Yamaha' => 'Yamaha',
                                                        'Kawasaki' => 'Kawasaki',
                                                        'Mitsubishi' => 'Mitsubishi',
                                                        'Daihatsu' => 'Daihatsu',
                                                        'Other' => 'Lainnya',
                                                    ])
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => 'Merk kendaraan harus dipilih',
                                                        'in' => 'Merk kendaraan tidak valid',
                                                    ])
                                                    ->rules(['required', 'in:Honda,Toyota,Nissan,Suzuki,Yamaha,Kawasaki,Mitsubishi,Daihatsu,Other']),

                                                Forms\Components\TextInput::make('detail')
                                                    ->label('Detail Kendaraan')
                                                    ->placeholder('Contoh: Isuzu Panther Touring, Toyota Kijang Innova V 2.4 atau lainnya')
                                                    ->helperText('Masukkan detail spesifik kendaraan seperti tipe dan varian')
                                                    ->maxLength(255)
                                                    ->validationMessages([
                                                        'max' => 'Detail kendaraan maksimal 255 karakter',
                                                    ])
                                                    ->rules(['nullable', 'max:255', 'string'])
                                                    ->columnSpanFull(),

                                            ])->columns(2),

                                        Forms\Components\Section::make('Data Kepemilikan')
                                            ->schema([
                                                Forms\Components\TextInput::make('owner')
                                                    ->label('Pemilik')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->validationMessages([
                                                        'required' => 'Nama pemilik harus diisi',
                                                        'max' => 'Nama pemilik maksimal 255 karakter',
                                                        'string' => 'Nama pemilik harus berupa teks',
                                                    ])
                                                    ->rules(['required', 'string', 'max:255']),

                                                Forms\Components\Select::make('ownership_type')
                                                    ->label('Jenis Kepemilikan')
                                                    ->options([
                                                        'Inventaris' => 'Inventaris',
                                                        'Pribadi' => 'Pribadi',
                                                    ])
                                                    ->required()
                                                    ->default('Inventaris')
                                                    ->validationMessages([
                                                        'required' => 'Jenis kepemilikan harus dipilih',
                                                        'in' => 'Jenis kepemilikan tidak valid',
                                                    ])
                                                    ->rules(['required', 'in:Inventaris,Pribadi']),

                                                Forms\Components\Toggle::make('isactive')
                                                    ->label('Status Aktif')
                                                    ->required()
                                                    ->inline(false)
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->default(true)
                                                    ->validationMessages([
                                                        'required' => 'Status aktif harus dipilih',
                                                        'boolean' => 'Status aktif tidak valid',
                                                    ])
                                                    ->rules(['required', 'boolean']),
                                            ])->columns(2),
                                    ])
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        if ($state) {
                                            $vehicle = \App\Models\Vehicle::with('vehicleType')->find($state);
                                            if ($vehicle) {
                                                $set('license_plate', $vehicle->license_plate);
                                                $set('vehicle_type_id', $vehicle->vehicle_type_id);
                                                $set('vehicle_type_name', $vehicle->vehicleType->name);
                                                $set('owner', $vehicle->owner);
                                                $set('vehicle_model_name', $vehicle->vehicle_model);
                                                $set('brand_name', $vehicle->brand);
                                                $set('detail_name', $vehicle->detail);
                                                $set('ownership_type_name', $vehicle->ownership_type);
                                            }
                                        } else {
                                            $set('license_plate', null);
                                            $set('vehicle_type_id', null);
                                            $set('vehicle_type_name', null);
                                            $set('owner', null);
                                            $set('vehicle_model_name', null);
                                            $set('brand_name', null);
                                            $set('detail_name', null);
                                            $set('ownership_type_name', null);
                                        }
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('vehicle_type_name')
                                            ->label('Jenis Kendaraan')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('owner')
                                            ->label('Pemilik')
                                            ->disabled()
                                            ->dehydrated(true),
                                        Forms\Components\TextInput::make('brand_name')
                                            ->label('Merk')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('vehicle_model_name')
                                            ->label('Model')
                                            ->disabled()
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('ownership_type_name')
                                            ->label('Kepemilikan')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefix(fn($state) => $state === 'Inventaris' ? '🏢' : '👤'),
                                        Forms\Components\TextInput::make('detail_name')
                                            ->label('Detail Kendaraan')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('-'),
                                    ]),
                            ]),

                        Forms\Components\Hidden::make('vehicle_type_id'),
                        Forms\Components\Hidden::make('license_plate'),
                    ]),

                Forms\Components\Section::make('Informasi Pengisian & Keterangan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->label('Jumlah')
                                    ->placeholder('Masukkan jumlah')
                                    ->mask('999999999')
                                    ->live(onBlur: true, debounce: 500)
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

                                Forms\Components\TextInput::make('volume')
                                    ->label('Volume BBM')
                                    ->suffix('Liter')
                                    ->disabled()
                                    ->dehydrated(true),

                                Forms\Components\Placeholder::make('balance_warning')
                                    ->label('')
                                    ->content(fn(Forms\Get $get) => $get('balance_warning'))
                                    ->extraAttributes([
                                        'class' => 'text-danger-500 font-medium',
                                    ])
                                    ->visible(fn(Forms\Get $get) => $get('balance_warning'))
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('usage_description')
                                    ->required()
                                    ->label('Keterangan Penggunaan')
                                    ->placeholder('Masukkan keterangan penggunaan BBM')
                                    ->rules(['required', 'min:10'])
                                    ->validationMessages([
                                        'required' => 'Keterangan wajib diisi',
                                        'min' => 'Keterangan minimal 10 karakter',
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ]),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
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
                                        'image/heic',
                                        'image.heif'
                                    ])
                                    ->maxSize(10240)
                                    ->directory('fuel-receipts')
                                    ->optimize('jpg')
                                    ->label('Struk BBM')
                                    ->uploadButtonPosition('left')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadProgressIndicatorPosition('left')
                                    ->panelLayout('integrated')
                                    ->extraAttributes([
                                        'accept' => 'image/*',
                                        'capture' => 'environment'
                                    ])
                                    ->validationMessages([
                                        'image' => 'File harus berupa gambar',
                                        'max' => 'Ukuran file maksimal 10MB',
                                    ])
                                    ->helperText(fn() => new \Illuminate\Support\HtmlString(
                                        '<div class="mt-1">' . view('components.filament.camera-capture')->render() . '</div>'
                                    )),

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
                                        'image/png',
                                        'image/jpg',
                                        'image.heic',
                                        'image.heif'
                                    ])
                                    ->maxSize(10240)
                                    ->directory('invoices')
                                    ->optimize('jpg')
                                    ->label('Form Permintaan BBM')
                                    ->uploadButtonPosition('left')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadProgressIndicatorPosition('left')
                                    ->panelLayout('integrated')
                                    ->extraAttributes([
                                        'accept' => 'image/*',
                                        'capture' => 'environment'
                                    ])
                                    ->validationMessages([
                                        'image' => 'File harus berupa gambar',
                                        'max' => 'Ukuran file maksimal 10MB',
                                    ])
                                    ->helperText(fn() => new \Illuminate\Support\HtmlString(
                                        '<div class="mt-1">' . view('components.filament.camera-capture')->render() . '</div>'
                                    )),
                            ]),
                    ]),

                Forms\Components\Hidden::make('balance_id')
                    ->default(function () {
                        return Balance::latest()->first()?->id ?? null;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at'))
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
                        $model = $record->vehicle->vehicle_model ?? '-';
                        $brand = $record->vehicle->brand ?? '-';

                        return "{$owner}<br>
                                {$plate}<br>
                                {$brand} - {$model}";
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
                                fn($query) => $query->select(['id', 'license_plate', 'owner'])
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->license_plate} - {$record->owner}")
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
                            fn() => print($pdf->output()),
                            'laporan-transaksi-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),

                Tables\Actions\Action::make('generateCashBook')
                    ->label('Buku Kas BBM')
                    ->color('info')
                    ->icon('heroicon-o-book-open')
                    ->form([
                        Forms\Components\Select::make('fuel_type_id')
                            ->label('Jenis BBM')
                            ->relationship('fuelType', 'name')
                            ->placeholder('Semua Jenis BBM')
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
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = Carbon::parse($data['end_date']);

                        // Get transactions and balances
                        $query = Transaction::query()
                            ->with(['fuelType', 'balance'])
                            ->whereBetween('usage_date', [$startDate, $endDate]);

                        if (!empty($data['fuel_type_id'])) {
                            $query->where('fuel_type_id', $data['fuel_type_id']);
                        }

                        $transactions = $query->orderBy('usage_date', 'asc')
                            ->orderBy('created_at', 'asc')
                            ->get();

                        // Get deposits
                        $depositsQuery = Balance::query()
                            ->whereBetween('date', [$startDate, $endDate]);

                        if (!empty($data['fuel_type_id'])) {
                            $depositsQuery->where('fuel_type_id', $data['fuel_type_id']);
                        }

                        $deposits = $depositsQuery->orderBy('date', 'asc')
                            ->orderBy('created_at', 'asc')
                            ->get();

                        // Prepare data by fuel type
                        $cashBookData = [];
                        $fuelTypes = !empty($data['fuel_type_id'])
                            ? [FuelType::find($data['fuel_type_id'])]
                            : FuelType::all();

                        foreach ($fuelTypes as $fuelType) {
                            // Get initial balance (last balance before start date)
                            $initialBalance = Balance::where('fuel_type_id', $fuelType->id)
                                ->where('date', '<', $startDate)
                                ->latest()
                                ->first()?->remaining_balance ?? 0;

                            $fuelTransactions = [];

                            // Add deposits as debit transactions
                            foreach ($deposits->where('fuel_type_id', $fuelType->id) as $deposit) {
                                // Ensure date is a Carbon instance before formatting
                                $depositDate = $deposit->date instanceof Carbon
                                    ? $deposit->date
                                    : Carbon::parse($deposit->date);

                                $fuelTransactions[] = [
                                    'date' => $depositDate->format('d/m/Y'),
                                    'number' => 'DEP-' . $deposit->id,
                                    'description' => 'Pengisian Kas BBM',
                                    'debit' => $deposit->deposit_amount,
                                    'credit' => null
                                ];
                            }

                            // Add fuel usage as credit transactions
                            foreach ($transactions->where('fuel_type_id', $fuelType->id) as $transaction) {
                                // Ensure usage_date is a Carbon instance before formatting
                                $usageDate = $transaction->usage_date instanceof Carbon
                                    ? $transaction->usage_date
                                    : Carbon::parse($transaction->usage_date);

                                $fuelTransactions[] = [
                                    'date' => $usageDate->format('d/m/Y'),
                                    'number' => $transaction->transaction_number,
                                    'description' => $transaction->usage_description,
                                    'debit' => null,
                                    'credit' => $transaction->amount
                                ];
                            }

                            // Sort combined transactions by date
                            usort($fuelTransactions, function ($a, $b) {
                                return strtotime($a['date']) - strtotime($b['date']);
                            });

                            $cashBookData[$fuelType->name] = [
                                'initial_balance' => $initialBalance,
                                'transactions' => $fuelTransactions
                            ];
                        }

                        $totalDeposits = $deposits->sum('deposit_amount');
                        $totalUsage = $transactions->sum('amount');
                        $initialBalance = array_sum(array_column($cashBookData, 'initial_balance'));
                        $totalCash = $initialBalance + $totalDeposits;
                        $finalBalance = $totalCash - $totalUsage;

                        $dateRange = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');

                        $company = CompanySetting::first();
                        $selectedFuelType = !empty($data['fuel_type_id'])
                            ? FuelType::find($data['fuel_type_id'])->name
                            : null;

                        $pdf = Pdf::loadView('reports.cash-book', [
                            'cashBookData' => $cashBookData,
                            'company' => $company,
                            'dateRange' => $dateRange,
                            'startDate' => $startDate->format('d/m/Y'),
                            'selectedFuelType' => $selectedFuelType,
                            'initialBalance' => $initialBalance,
                            'totalDeposits' => $totalDeposits,
                            'totalCash' => $totalCash,
                            'totalUsage' => $totalUsage,
                            'finalBalance' => $finalBalance,
                        ]);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'buku-kas-bbm-' . now()->format('Y-m-d') . '.pdf'
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
                        ->form([
                            Forms\Components\Textarea::make('deletion_reason')
                                ->label('Alasan Penghapusan')
                                ->required()
                                ->minLength(10)
                                ->maxLength(500)
                                ->placeholder('Masukkan alasan penghapusan transaksi')
                                ->validationMessages([
                                    'required' => 'Alasan penghapusan wajib diisi',
                                    'min' => 'Alasan penghapusan minimal 10 karakter',
                                    'max' => 'Alasan penghapusan maksimal 500 karakter',
                                ])
                        ])
                        ->before(function (Transaction $record, array $data) {
                            session(['deletion_reason' => $data['deletion_reason']]);
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
            'edit' => Pages\EditTransaction::route('/{record}/edit'), // Add comma here
        ];
    }
}

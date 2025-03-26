<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Filament\Resources\VehicleResource\Widgets\VehicleStatsOverview;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Data Master Kendaraan';
    protected static ?int $navigationSort = 2;
    protected static ?int $navigationGroupSort = 2;
    protected static ?string $navigationLabel = 'Data Kendaraan';
    protected static ?string $modelLabel = 'Kendaraan';
    protected static ?string $pluralModelLabel = 'Kendaraan';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make('Data Kepemilikan')
                            ->description('Masukkan informasi data kepemilikan kendaraan')
                            ->collapsible()
                            ->schema([
                                Forms\Components\TextInput::make('owner')
                                    ->label('Pemilik')
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Nama pemilik harus diisi',
                                    ]),
                                Forms\Components\Select::make('ownership_type')
                                    ->label('Jenis Kepemilikan')
                                    ->options([
                                        'Inventaris' => 'Inventaris',
                                        'Pribadi' => 'Pribadi',
                                    ])
                                    ->required()
                                    ->default('Inventaris'),
                                Forms\Components\Toggle::make('isactive')
                                    ->label('Status Aktif')
                                    ->required()
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ])->columnSpan(1),
                        Forms\Components\Section::make('Informasi Kendaraan')
                            ->description('Masukkan informasi detail kendaraan')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Select::make('vehicle_type_id')
                                    ->relationship('vehicleType', 'name')
                                    ->label('Jenis Kendaraan')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Jenis kendaraan harus dipilih',
                                    ]),
                                Forms\Components\Select::make('fuel_type_id')
                                    ->relationship('fuelType', 'name')
                                    ->label('Jenis Bahan Bakar')
                                    ->validationMessages([
                                        'exists' => 'Jenis bahan bakar tidak valid',
                                    ]),
                                Forms\Components\TextInput::make('license_plate')
                                    ->label('Nomor Kendaraan')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->regex('/^[A-Z]{1,2}\s*\d{1,4}\s*[A-Z]{1,3}$/')
                                    ->validationMessages([
                                        'required' => 'Nomor Kendaraan harus diisi',
                                        'regex' => 'Format Nomor Kendaraan tidak valid',
                                        'unique' => 'Nomor Kendaraan sudah terdaftar',
                                    ]),
                                Forms\Components\Select::make('vehicle_model')
                                    ->label('Tipe Kendaraan')
                                    ->options([
                                        'Pickup' => 'Pickup',
                                        'Bebek' => 'Bebek',
                                        'Matic' => 'Matic',
                                        'SUV' => 'SUV',
                                        'MPV' => 'MPV',
                                        'Sport' => 'Sport',
                                    ]),
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
                                    ]),
                                Forms\Components\TextInput::make('detail')
                                    ->label('Detail Kendaraan')
                                    ->placeholder('Contoh: Isuzu Panther Touring, Toyota Kijang Innova V 2.4 atau lainnya')
                                    ->helperText('Masukkan detail spesifik kendaraan seperti tipe dan varian')
                                    ->columnSpanFull(),
                            ])->columns(2)->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor Kendaraan berhasil disalin')
                    ->description(
                        fn($record): string =>
                        $record->owner . ' - ' . $record->vehicleType->name
                    ),
                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis Bahan Bakar')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_model')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detail')
                    ->label('Detail')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->brand . ' ' . $record->vehicle_model),

                Tables\Columns\TextColumn::make('ownership_type')
                    ->label('Kepemilikan')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('isactive')
                    ->label('Status')
                    ->onColor('success')
                    ->offColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type_id')
                    ->relationship('vehicleType', 'name')
                    ->label('Jenis Kendaraan')
                    ->placeholder('Semua Jenis'),
                Tables\Filters\SelectFilter::make('fuel_type_id')
                    ->relationship('fuelType', 'name')
                    ->label('Jenis Bahan Bakar')
                    ->placeholder('Semua Jenis BBM'),
                Tables\Filters\TernaryFilter::make('isactive')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
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
                        ->modalHeading('Hapus Kendaraan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data kendaraan ini?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->dropdown()
                    ->button()
                    ->color('primary')
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus Kendaraan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data kendaraan yang dipilih?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            VehicleStatsOverview::class
        ];
    }
}

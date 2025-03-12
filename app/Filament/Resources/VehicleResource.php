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
    protected static ?int $navigationGroupSort = 2; // Changed from ?string to ?int
    protected static ?string $navigationLabel = 'Data Kendaraan';
    protected static ?string $modelLabel = 'Kendaraan';
    protected static ?string $pluralModelLabel = 'Kendaraan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kendaraan')
                    ->description('Masukkan informasi detail kendaraan')
                    ->schema([
                        Forms\Components\Select::make('vehicle_type_id')
                            ->relationship('vehicleType', 'name')
                            ->label('Jenis Kendaraan')
                            ->required()
                            ->validationMessages([
                                'required' => 'Jenis kendaraan harus dipilih',
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
                    ])->columns(2),

                Forms\Components\Section::make('Data Kepemilikan')
                    ->schema([
                        Forms\Components\TextInput::make('owner')
                            ->label('Pemilik')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Nama pemilik harus diisi',
                            ]),
                        Forms\Components\Toggle::make('isactive')
                            ->label('Status Aktif')
                            ->required()
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicleType.name')
                    ->label('Jenis Kendaraan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor Kendaraan berhasil disalin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner')
                    ->label('Pemilik')
                    ->searchable()
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuelResource\Pages;
use App\Filament\Resources\FuelResource\RelationManagers;
use App\Models\Fuel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FuelResource extends Resource
{
    protected static ?string $model = Fuel::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationGroup = 'Manajemen BBM';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Data BBM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data BBM')
                    ->description('Informasi utama BBM')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('fuel_type_id')
                            ->relationship('fuelType', 'name')
                            ->label('Jenis BBM')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Jenis BBM')
                                    ->required(),
                            ])
                            ->rules(['required', 'exists:fuel_types,id'])
                            ->validationMessages([
                                'required' => 'Jenis BBM harus dipilih',
                                'exists' => 'Jenis BBM tidak valid',
                            ]),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama BBM')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama BBM')
                            ->rules(['required', 'string', 'max:255'])
                            ->validationMessages([
                                'required' => 'Nama BBM harus diisi',
                                'max' => 'Nama BBM maksimal 255 karakter',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Harga')
                    ->description('Detail harga dan satuan BBM')
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Harga BBM')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->mask('999999999')
                            ->rules(['required', 'numeric', 'min:0'])
                            ->validationMessages([
                                'numeric' => 'Harga harus berupa angka',
                                'min' => 'Harga tidak boleh negatif',
                            ]),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(255)
                            ->placeholder('Contoh: Liter')
                            ->rules(['required', 'string', 'max:255'])
                            ->validationMessages([
                                'max' => 'Satuan maksimal 255 karakter',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->description('Pengaturan status BBM')
                    ->icon('heroicon-o-check-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Toggle::make('isactive')
                            ->label('Status Aktif')
                            ->required()
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->helperText('Aktifkan atau nonaktifkan BBM')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fuelType.name')
                    ->label('Jenis BBM')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama BBM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->searchable(),
                Tables\Columns\IconColumn::make('isactive')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d F Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fuel_type')
                    ->relationship('fuelType', 'name')
                    ->label('Jenis BBM'),
                Tables\Filters\TernaryFilter::make('isactive')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->placeholder('Semua Status'),
                Tables\Filters\TrashedFilter::make()
                    ->label('Terhapus'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus BBM')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data BBM ini?'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Pulihkan'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus BBM Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data BBM yang dipilih?'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuels::route('/'),
            'create' => Pages\CreateFuel::route('/create'),
            'edit' => Pages\EditFuel::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuelTypeResource\Pages;
use App\Filament\Resources\FuelTypeResource\RelationManagers;
use App\Models\FuelType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FuelTypeResource extends Resource
{
    protected static ?string $model = FuelType::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Manajemen Kas BBM';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Jenis BBM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jenis BBM')
                    ->description('Masukkan informasi detail jenis BBM')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Jenis BBM')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['required', 'string', 'max:255'])
                            ->placeholder('Masukkan nama jenis BBM')
                            ->validationMessages([
                                'required' => 'Nama jenis BBM wajib diisi',
                                'max' => 'Nama jenis BBM maksimal 255 karakter',
                                'unique' => 'Nama jenis BBM sudah ada',
                            ]),

                        Forms\Components\Textarea::make('desc')
                            ->label('Deskripsi')
                            ->rules(['nullable', 'string'])
                            ->placeholder('Masukkan deskripsi jenis BBM')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status')
                    ->description('Atur status aktif jenis BBM')
                    ->schema([
                        Forms\Components\Toggle::make('isactive')
                            ->label('Status Aktif')
                            ->required()
                            ->default(true)
                            ->helperText('Aktifkan atau nonaktifkan jenis BBM ini')
                            ->validationMessages([
                                'required' => 'Status aktif wajib dipilih',
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jenis BBM')
                    ->searchable()
                    ->sortable()
                    ->tooltip('Nama Jenis BBM'),

                Tables\Columns\IconColumn::make('isactive')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('isactive')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Data Terhapus'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Jenis BBM')
                        ->modalDescription('Apakah Anda yakin ingin menghapus jenis BBM ini?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus Jenis BBM Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus jenis BBM yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
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
            'index' => Pages\ListFuelTypes::route('/'),
            'create' => Pages\CreateFuelType::route('/create'),
            'edit' => Pages\EditFuelType::route('/{record}/edit'),
        ];
    }
}

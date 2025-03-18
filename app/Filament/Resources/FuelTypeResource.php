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
use Illuminate\Support\Str;

class FuelTypeResource extends Resource
{
    protected static ?string $model = FuelType::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Data Master BBM';
    protected static ?int $navigationSort = 1;
    protected static ?int $navigationGroupSort = 1; // Changed from ?string to ?int
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

                        Forms\Components\TextInput::make('max_deposit')
                            ->label('Maksimal Deposit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('0.00')
                            ->helperText('Masukkan jumlah maksimal saldo yang dapat di depositkan')
                            ->live(onBlur: true)
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $terbilang = ucwords(self::terbilang((int)$state) . ' rupiah');
                                    $set('max_deposit_terbilang', $terbilang);
                                }
                            })
                            ->validationMessages([
                                'required' => 'Maksimal deposit wajib diisi',
                                'numeric' => 'Maksimal deposit harus berupa angka',
                                'min' => 'Maksimal deposit tidak boleh negatif',
                            ]),

                        Forms\Components\TextInput::make('max_deposit_terbilang')
                            ->label('Terbilang')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Hasil terbilang akan muncul otomatis'),

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

                Tables\Columns\TextColumn::make('max_deposit')
                    ->label('Maksimal Deposit')
                    ->money('IDR')
                    ->sortable()
                    ->tooltip('Jumlah maksimal saldo yang dapat di depositkan'),

                Tables\Columns\TextColumn::make('max_deposit_terbilang')
                    ->label('Terbilang')
                    ->state(function (FuelType $record): string {
                        // Capitalize each word in the result
                        return ucwords(self::terbilang((int)$record->max_deposit) . ' rupiah');
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->wrap()
                    ->tooltip('Jumlah maksimal saldo dalam kata-kata')
                    ->color('success'),

                Tables\Columns\ToggleColumn::make('isactive')
                    ->label('Status Aktif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        ->modalHeading('Hapus Jenis BBM')
                        ->modalDescription('Apakah Anda yakin ingin menghapus jenis BBM ini?')
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
                        ->modalHeading('Hapus Jenis BBM Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus jenis BBM yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ]);
    }

    // Fungsi helper untuk mengubah angka menjadi kata-kata (terbilang)
    protected static function terbilang(int $number): string
    {
        $words = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
        ];

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return self::terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            return self::terbilang((int)($number / 10)) . ' puluh ' . self::terbilang($number % 10);
        } elseif ($number < 200) {
            return 'seratus ' . self::terbilang($number - 100);
        } elseif ($number < 1000) {
            return self::terbilang((int)($number / 100)) . ' ratus ' . self::terbilang($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . self::terbilang($number - 1000);
        } elseif ($number < 1000000) {
            return self::terbilang((int)($number / 1000)) . ' ribu ' . self::terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            return self::terbilang((int)($number / 1000000)) . ' juta ' . self::terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            return self::terbilang((int)($number / 1000000000)) . ' milyar ' . self::terbilang($number % 1000000000);
        } else {
            return self::terbilang((int)($number / 1000000000000)) . ' trilyun ' . self::terbilang($number % 1000000000000);
        }
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

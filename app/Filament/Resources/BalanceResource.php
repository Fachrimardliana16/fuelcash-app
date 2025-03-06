<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Saldo';
    protected static ?string $navigationGroup = 'Keuangan';

    public static function form(Form $form): Form
    {
        $lastBalance = Balance::latest()->first()?->remaining_balance ?? 0;

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Deposit')
                    ->description('Masukkan detail deposit')
                    ->schema([
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
                            ->mask('999999999')
                            ->placeholder('Masukkan jumlah deposit')
                            ->live(debounce: 300)
                            ->minValue(1)
                            ->validationMessages([
                                'required' => 'Silakan masukkan jumlah deposit',
                                'numeric' => 'Jumlah harus berupa angka',
                                'min' => 'Jumlah harus lebih besar dari 0',
                            ])
                            ->afterStateUpdated(function ($state, Forms\Set $set) use ($lastBalance) {
                                $newBalance = $lastBalance + ($state ?? 0);
                                $set('remaining_balance', $newBalance);
                            }),
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
                            ->content('Rp ' . number_format($lastBalance, 0, ',', '.'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                BalanceResource\Actions\ReportAction::make('generateBalanceReport')
                    ->label('Buat Laporan'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Ubah'),
                Tables\Actions\Action::make('pdf')
                    ->label('Ekspor PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Balance $record) {
                        $pdf = Pdf::loadView('pdf.balance-report', [
                            'balance' => $record,
                            'transactions' => $record->transactions
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'balance-report-' . $record->date . '.pdf');
                    }),
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
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}

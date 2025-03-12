<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Balance;
use Filament\Forms\Form;
use App\Models\Signature;
use Filament\Tables\Table;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BalanceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BalanceResource\RelationManagers;

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
                            ->inputMode('numeric') // Add this
                            ->placeholder('Masukkan jumlah deposit')
                            ->live(debounce: 300)
                            ->minValue(1)
                            // Removed step(1000)
                            ->validationMessages([
                                'required' => 'Silakan masukkan jumlah deposit',
                                'numeric' => 'Jumlah harus berupa angka',
                                'min' => 'Jumlah harus lebih besar dari 0',
                            ])
                            ->afterStateUpdated(function ($state, Forms\Set $set) use ($lastBalance) {
                                $newBalance = $lastBalance + (float)($state ?? 0);
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
                Tables\Actions\Action::make('generateBalanceReport')
                    ->label('Buat Laporan')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $balances = Balance::with('transactions')
                            ->orderBy('date', 'desc')
                            ->get();

                        $company = CompanySetting::first();

                        $pdf = Pdf::loadView('pdf.balance-report', [
                            'balances' => $balances,
                            'total_deposit' => $balances->sum('deposit_amount'),
                            'current_balance' => $balances->last()->remaining_balance,
                            'company' => $company
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'laporan-saldo-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Ubah'),
                Tables\Actions\Action::make('request-letter')
                    ->label('Surat Pengajuan')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (Balance $record) {
                        $terbilang = new \App\Helpers\Terbilang();
                        $company = CompanySetting::first();

                        $pdf = Pdf::loadView('pdf.fuel-request-letter', [
                            'balance' => $record,
                            'terbilang' => $terbilang->convert($record->deposit_amount),
                            'company' => $company,
                            'signatures' => Signature::orderBy('order')->get()
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'surat-pengajuan-bbm-' . $record->date . '.pdf');
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
            'view' => Pages\ViewBalance::route('/{record}'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}

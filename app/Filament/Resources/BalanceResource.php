<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BalanceResource\Pages;
use App\Filament\Resources\BalanceResource\RelationManagers;
use App\Models\Balance;
use App\Models\CompanySetting;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationLabel = 'Saldo Bahan Bakar';
    protected static ?string $navigationGroup = 'Manajemen Kas BBM';
    protected static ?int $navigationSort = 1;
    protected static ?int $navigationGroupSort = 3;

    public static function form(Form $form): Form
    {
        $lastBalance = Balance::latest()->first()?->remaining_balance ?? 0;

        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

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
                            ->inputMode('numeric')
                            ->placeholder('Masukkan jumlah deposit')
                            ->live(onBlur: true, debounce: 500) // Updated to use onBlur and debounce
                            ->minValue(1)
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

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable(),

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
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $balances = Balance::with(['transactions', 'user'])
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

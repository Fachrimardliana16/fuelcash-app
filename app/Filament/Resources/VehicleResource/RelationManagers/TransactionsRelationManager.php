<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $title = 'Riwayat Transaksi';
    protected static ?string $recordTitleAttribute = 'usage_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('usage_date')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('usage_date')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fuel.name')
                    ->label('Bahan Bakar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_description')
                    ->label('Keterangan')
                    ->limit(30),

                Tables\Columns\ImageColumn::make('fuel_receipt')
                    ->label('Struk')
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Transaksi'),

                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
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
                    ->action(function (array $data, RelationManager $livewire) {
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = \Carbon\Carbon::parse($data['end_date']);

                        $vehicle = $livewire->getOwnerRecord();

                        $transactions = $vehicle->transactions()
                            ->with(['fuel', 'fuelType'])
                            ->whereBetween('usage_date', [
                                $startDate->startOfDay(),
                                $endDate->endOfDay()
                            ])
                            ->orderBy('usage_date', 'asc')
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

                        $pdf = Pdf::loadView('reports.vehicle-transactions', [
                            'vehicle' => $vehicle,
                            'transactions' => $transactions,
                            'dateRange' => $dateRange,
                            'totalAmount' => $transactions->sum('amount')
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'transaksi-kendaraan-' . $vehicle->license_plate . '-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Transaksi')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data transaksi ini?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('usage_date', 'desc');
    }
}

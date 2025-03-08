<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Actions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $transaction = $this->record;

                    // Load the transaction with its relationships
                    $transaction->load(['vehicle.vehicleType', 'fuelType', 'fuel', 'balance']);

                    // Convert images to base64 if they exist
                    $fuelReceiptBase64 = null;
                    $invoiceBase64 = null;

                    if ($transaction->fuel_receipt && Storage::disk('public')->exists($transaction->fuel_receipt)) {
                        try {
                            $fuelReceiptContents = Storage::disk('public')->get($transaction->fuel_receipt);
                            $fuelReceiptBase64 = 'data:image/jpeg;base64,' . base64_encode($fuelReceiptContents);
                        } catch (\Exception $e) {
                            // Silent fail - just don't include the image
                        }
                    }

                    if ($transaction->invoice && Storage::disk('public')->exists($transaction->invoice)) {
                        try {
                            $invoiceContents = Storage::disk('public')->get($transaction->invoice);
                            $invoiceBase64 = 'data:image/jpeg;base64,' . base64_encode($invoiceContents);
                        } catch (\Exception $e) {
                            // Silent fail - just don't include the image
                        }
                    }

                    // Set Indonesian locale for dates
                    Carbon::setLocale('id');

                    $pdf = Pdf::loadView('reports.transaction-detail', [
                        'transaction' => $transaction,
                        'fuelReceiptBase64' => $fuelReceiptBase64,
                        'invoiceBase64' => $invoiceBase64,
                        'currentUser' => Auth::user(),
                    ]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'transaksi-' . $this->record->id . '-' . Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d') . '.pdf');
                }),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Data Kendaraan')
                    ->description('Informasi kendaraan yang digunakan')
                    ->schema([
                        Components\TextEntry::make('vehicle.license_plate')
                            ->label('Plat Nomor'),
                        Components\TextEntry::make('vehicle.vehicleType.name')
                            ->label('Jenis Kendaraan'),
                        Components\TextEntry::make('owner')
                            ->label('Pemilik'),
                    ])->columns(3),

                Components\Section::make('Data Penggunaan BBM')
                    ->description('Informasi penggunaan bahan bakar')
                    ->schema([
                        Components\TextEntry::make('usage_date')
                            ->label('Tanggal Penggunaan')
                            ->date('d F Y'),
                        Components\TextEntry::make('fuelType.name')
                            ->label('Jenis BBM'),
                        Components\TextEntry::make('fuel.name')
                            ->label('BBM'),
                        Components\TextEntry::make('amount')
                            ->label('Jumlah')
                            ->money('idr'),
                    ])->columns(2),

                Components\Section::make('Keterangan & Dokumen')
                    ->description('Informasi tambahan dan dokumen pendukung')
                    ->schema([
                        Components\TextEntry::make('usage_description')
                            ->label('Keterangan Penggunaan')
                            ->columnSpanFull(),
                        Components\ImageEntry::make('fuel_receipt')
                            ->label('Struk BBM')
                            ->columnSpanFull()
                            ->height(400)
                            ->visible(fn($record) => !empty($record->fuel_receipt)),
                        Components\ImageEntry::make('invoice')
                            ->label('Nota/Kwitansi')
                            ->columnSpanFull()
                            ->height(400)
                            ->visible(fn($record) => !empty($record->invoice)),
                    ]),

                Components\Section::make('Informasi Sistem')
                    ->description('Data yang direkam oleh sistem')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i:s'),
                        Components\TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y, H:i:s'),
                        Components\TextEntry::make('balance.remaining_balance')
                            ->label('Sisa Saldo')
                            ->money('idr')
                            ->color('success'),
                    ])->columns(3)->collapsed(),
            ]);
    }
}

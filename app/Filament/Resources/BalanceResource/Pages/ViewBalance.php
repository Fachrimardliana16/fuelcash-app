<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Colors\Color;

class ViewBalance extends ViewRecord
{
    protected static string $resource = BalanceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->getRecord();

        return $infolist
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Informasi Deposit')
                            ->icon('heroicon-o-banknotes')
                            ->description('Detail deposit saldo')
                            ->schema([
                                Group::make([
                                    TextEntry::make('date')
                                        ->label('Tanggal Input')
                                        ->date('d F Y')
                                        ->icon('heroicon-o-calendar'),

                                    TextEntry::make('created_at')
                                        ->label('Dibuat Pada')
                                        ->dateTime('d F Y - H:i')
                                        ->icon('heroicon-o-clock'),
                                ])->columns(2),

                                TextEntry::make('deposit_amount')
                                    ->label('Jumlah Deposit')
                                    ->money('idr')
                                    ->color(Color::Green)
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->icon('heroicon-o-arrow-trending-up'),
                            ]),

                        Section::make('Informasi Saldo')
                            ->icon('heroicon-o-wallet')
                            ->description('Detail saldo terkini')
                            ->schema([
                                TextEntry::make('remaining_balance')
                                    ->label('Sisa Saldo')
                                    ->money('idr')
                                    ->color(fn($state) => $state > 1000000 ? Color::Green : Color::Red)
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->icon('heroicon-o-banknotes'),

                                Group::make([
                                    IconEntry::make('status_icon')
                                        ->label('Status')
                                        ->icon(fn() => $record->remaining_balance > 1000000 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                                        ->color(fn() => $record->remaining_balance > 1000000 ? Color::Green : Color::Red),

                                    TextEntry::make('status_label')
                                        ->label('')
                                        ->state(fn() => $record->remaining_balance > 1000000 ? 'Saldo Mencukupi' : 'Saldo Menipis')
                                        ->color(fn() => $record->remaining_balance > 1000000 ? Color::Green : Color::Red),
                                ])->columnSpanFull(),
                            ]),

                        Section::make('Transaksi Terkait')
                            ->icon('heroicon-o-receipt-percent')
                            ->collapsible()
                            ->description('Transaksi yang menggunakan deposit ini')
                            ->schema([
                                TextEntry::make('transactions_count')
                                    ->label('Jumlah Transaksi')
                                    ->getStateUsing(fn() => $record->transactions()->count())
                                    ->icon('heroicon-o-shopping-cart'),

                                TextEntry::make('transactions_total')
                                    ->label('Total Transaksi')
                                    ->getStateUsing(fn() => $record->transactions()->sum('amount'))
                                    ->money('idr')
                                    ->icon('heroicon-o-currency-rupee'),
                            ])->columns(2),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah')
                ->icon('heroicon-o-pencil-square')
                ->color('primary'),

            Actions\Action::make('request-letter')
                ->label('Cetak Surat Pengajuan')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action(function () {
                    $record = $this->getRecord();
                    $terbilang = new \App\Helpers\Terbilang();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.fuel-request-letter', [
                        'balance' => $record,
                        'terbilang' => $terbilang->convert($record->deposit_amount)
                    ]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'surat-pengajuan-bbm-' . $record->date . '.pdf');
                }),

            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}

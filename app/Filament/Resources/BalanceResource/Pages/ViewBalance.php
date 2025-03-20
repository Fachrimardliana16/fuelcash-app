<?php

namespace App\Filament\Resources\BalanceResource\Pages;

use App\Filament\Resources\BalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
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
                Grid::make(3)
                    ->schema([
                        // Main Summary Card - Spans 2 Columns
                        Card::make()
                            ->schema([
                                Split::make([
                                    Grid::make(2)
                                        ->schema([
                                            // Fuel Type Info
                                            Group::make([
                                                TextEntry::make('fuelType.name')
                                                    ->label('Jenis BBM')
                                                    ->icon('heroicon-o-beaker')
                                                    ->weight('bold')
                                                    ->size(TextEntry\TextEntrySize::Large),

                                                TextEntry::make('date')
                                                    ->label('Tanggal Input')
                                                    ->date('d F Y')
                                                    ->icon('heroicon-o-calendar'),
                                            ]),

                                            // Deposit Info with prominent display
                                            Group::make([
                                                TextEntry::make('deposit_amount')
                                                    ->label('Jumlah Deposit')
                                                    ->money('idr')
                                                    ->weight('bold')
                                                    ->color(Color::Green)
                                                    ->size(TextEntry\TextEntrySize::Large)
                                                    ->icon('heroicon-o-arrow-trending-up'),

                                                TextEntry::make('user.name')
                                                    ->label('Diinput Oleh')
                                                    ->icon('heroicon-o-user'),
                                            ]),
                                        ]),
                                ]),

                                // Balance Information
                                Group::make([
                                    TextEntry::make('remaining_balance')
                                        ->label('Sisa Saldo')
                                        ->money('idr')
                                        ->color(fn($state) => $state > 1000000 ? Color::Green : Color::Red)
                                        ->weight('bold')
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->icon('heroicon-o-banknotes'),

                                    Group::make([
                                        IconEntry::make('status_icon')
                                            ->label('Status Saldo')
                                            ->icon(fn() => $record->remaining_balance > 1000000 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                                            ->color(fn() => $record->remaining_balance > 1000000 ? Color::Green : Color::Red),

                                        TextEntry::make('status_label')
                                            ->label('')
                                            ->state(fn() => $record->remaining_balance > 1000000 ? 'Saldo Mencukupi' : 'Saldo Menipis')
                                            ->weight('medium')
                                            ->color(fn() => $record->remaining_balance > 1000000 ? Color::Green : Color::Red),
                                    ])->columns(2),
                                ]),
                            ])
                            ->columnSpan(2),

                        // Detail Sidebar - Spans 1 Column
                        Group::make([
                            Card::make()
                                ->schema([
                                    Section::make('Deposit Details')
                                        ->heading('Detail Informasi')
                                        ->icon('heroicon-o-information-circle')
                                        ->schema([
                                            TextEntry::make('created_at')
                                                ->label('Dibuat Pada')
                                                ->dateTime('d F Y - H:i')
                                                ->icon('heroicon-o-clock'),

                                            TextEntry::make('max_deposit')
                                                ->label('Batas Maksimal')
                                                ->money('idr')
                                                ->getStateUsing(fn() => $record->fuelType->max_deposit)
                                                ->icon('heroicon-o-arrow-up'),

                                            TextEntry::make('last_balance')
                                                ->label('Saldo Sebelumnya')
                                                ->money('idr')
                                                ->getStateUsing(function () use ($record) {
                                                    $lastBalance = $record->remaining_balance - $record->deposit_amount;
                                                    return max(0, $lastBalance);
                                                })
                                                ->icon('heroicon-o-banknotes'),
                                        ]),
                                ]),

                            // Transaction Summary
                            Card::make()
                                ->schema([
                                    Section::make('Transaksi Terkait')
                                        ->icon('heroicon-o-receipt-percent')
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
                                        ]),
                                ]),
                        ])->columnSpan(1),
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
                    $company = \App\Models\CompanySetting::first();

                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.fuel-request-letter', [
                        'balance' => $record,
                        'terbilang' => $terbilang->convert($record->deposit_amount),
                        'company' => $company,
                        'signatures' => \App\Models\Signature::orderBy('order')->get()
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

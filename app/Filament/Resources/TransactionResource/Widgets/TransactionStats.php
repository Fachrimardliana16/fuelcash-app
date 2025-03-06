<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use App\Models\Balance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStats extends BaseWidget
{
    protected function getStats(): array
    {
        $latestBalance = Balance::latest()->first();

        return [
            Stat::make('Total Transaksi', Transaction::count())
                ->description('Jumlah transaksi yang tercatat')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format(Transaction::sum('amount'), 0, ',', '.'))
                ->description('Total pengeluaran BBM')
                ->icon('heroicon-o-banknotes')
                ->color('danger'),

            Stat::make('Sisa Saldo', 'Rp ' . number_format($latestBalance?->remaining_balance ?? 0, 0, ',', '.'))
                ->description('Saldo tersedia saat ini')
                ->icon('heroicon-o-wallet')
                ->color('success'),
        ];
    }
}

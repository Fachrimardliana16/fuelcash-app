<?php

namespace App\Filament\Resources\BalanceResource\Actions;

use App\Models\Balance;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generateBalanceReport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Generate Report')
            ->icon('heroicon-o-document-chart-bar')
            ->color('success')
            ->form([
                \Filament\Forms\Components\DatePicker::make('from')
                    ->label('From Date')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('until')
                    ->label('Until Date')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $balances = Balance::query()
                    ->whereDate('date', '>=', $data['from'])
                    ->whereDate('date', '<=', $data['until'])
                    ->with('transactions')
                    ->get();

                $pdf = Pdf::loadView('pdf.balance-report-range', [
                    'balances' => $balances,
                    'fromDate' => $data['from'],
                    'untilDate' => $data['until'],
                ]);

                $filename = 'balance-report-' . $data['from'] . '-to-' . $data['until'] . '.pdf';

                response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename)->send();
            });
    }
}

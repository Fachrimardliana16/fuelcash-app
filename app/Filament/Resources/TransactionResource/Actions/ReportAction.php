<?php

namespace App\Filament\Resources\TransactionResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Facades\FilamentAsset;

class ReportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generate-report';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Buat Laporan')
            ->icon('heroicon-o-document-chart-bar')
            ->color('success')
            ->form([
                \Filament\Forms\Components\DatePicker::make('start_date')
                    ->label('Dari Tanggal')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('end_date')
                    ->label('Sampai Tanggal')
                    ->required(),
            ])
            ->action(function (array $data) {
                $url = route('transactions.report', [
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                ]);

                // Using vanilla JavaScript for immediate download
                $this->evaluate(<<<JS
                    const link = document.createElement('a');
                    link.href = '{$url}';
                    link.download = 'transactions-report.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                JS);

                $this->success();
            });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Transaction::query()
            ->with(['vehicle.vehicleType', 'fuelType', 'fuel'])
            ->whereBetween('usage_date', [
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            ]);

        $transactions = $query->orderBy('usage_date')->get();

        $dateRange = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' .
            Carbon::parse($request->end_date)->format('d/m/Y');

        $pdf = Pdf::loadView('reports.transactions', [
            'transactions' => $transactions,
            'dateRange' => $dateRange
        ]);

        return $pdf->stream("transactions-report-{$dateRange}.pdf");
    }
}

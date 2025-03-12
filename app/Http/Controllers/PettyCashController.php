<?php

namespace App\Http\Controllers;

use App\Models\PettyCashDeposit;
use App\Models\PettyCashExpense;
use App\Models\PettyCashExpenseAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanySetting;
use Carbon\Carbon;

class PettyCashController extends Controller
{
    /**
     * Get current petty cash balance
     */
    public function getCurrentBalance()
    {
        $totalDeposits = PettyCashDeposit::sum('amount');
        $totalExpenses = PettyCashExpense::sum('amount');

        return $totalDeposits - $totalExpenses;
    }

    /**
     * Process a new expense using FIFO allocation
     */
    public function processExpense(PettyCashExpense $expense)
    {
        // Start a transaction to ensure data consistency
        return DB::transaction(function () use ($expense) {
            $remainingAmount = $expense->amount;

            // Get deposits with remaining amounts, ordered by oldest first (FIFO)
            $deposits = PettyCashDeposit::where('is_fully_used', false)
                ->orderBy('deposit_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($deposits as $deposit) {
                if ($remainingAmount <= 0) break;

                // Calculate how much to take from this deposit
                $allocationAmount = min($remainingAmount, $deposit->remaining_amount);

                // Create allocation record
                PettyCashExpenseAllocation::create([
                    'petty_cash_expense_id' => $expense->id,
                    'petty_cash_deposit_id' => $deposit->id,
                    'amount' => $allocationAmount
                ]);

                // Update deposit's remaining amount
                $deposit->remaining_amount -= $allocationAmount;

                // If deposit is fully used, mark it
                if ($deposit->remaining_amount <= 0) {
                    $deposit->is_fully_used = true;
                }

                $deposit->save();

                // Reduce the remaining expense amount
                $remainingAmount -= $allocationAmount;
            }

            // Check if we couldn't allocate the full expense amount
            if ($remainingAmount > 0) {
                throw new \Exception('Insufficient funds in petty cash to cover this expense.');
            }

            return true;
        });
    }

    /**
     * Generate expense report for a date range
     */
    public function generateExpenseReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get expenses in date range
        $expenses = PettyCashExpense::with(['user', 'deposits'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        // Get company information
        $company = CompanySetting::first();

        // Calculate totals
        $totalAmount = $expenses->sum('amount');

        // Generate PDF
        $pdf = PDF::loadView('reports.petty-cash-expenses', [
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $totalAmount,
            'company' => $company
        ]);

        return $pdf->download('laporan-kas-kecil-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate deposit report for a date range
     */
    public function generateDepositReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get deposits in date range
        $deposits = PettyCashDeposit::with(['user'])
            ->whereBetween('deposit_date', [$startDate, $endDate])
            ->orderBy('deposit_date', 'asc')
            ->get();

        // Get company information
        $company = CompanySetting::first();

        // Calculate totals
        $totalAmount = $deposits->sum('amount');
        $totalUsed = $deposits->sum(function ($deposit) {
            return $deposit->amount - $deposit->remaining_amount;
        });
        $totalRemaining = $deposits->sum('remaining_amount');

        // Generate PDF
        $pdf = PDF::loadView('reports.petty-cash-deposits', [
            'deposits' => $deposits,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $totalAmount,
            'totalUsed' => $totalUsed,
            'totalRemaining' => $totalRemaining,
            'company' => $company
        ]);

        return $pdf->download('laporan-setoran-kas-kecil-' . now()->format('Y-m-d') . '.pdf');
    }
}

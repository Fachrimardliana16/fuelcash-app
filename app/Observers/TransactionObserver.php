<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\DeletedTransaction;
use App\Models\Balance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class TransactionObserver
{
    public function creating(Transaction $transaction)
    {
        // Find the latest balance for the specific fuel type
        $latestBalance = Balance::where('fuel_type_id', $transaction->fuel_type_id)
            ->latest()
            ->first();

        if (!$latestBalance) {
            Notification::make()
                ->danger()
                ->title('Saldo Tidak Tersedia')
                ->body('Tidak ada saldo tersedia untuk jenis BBM yang dipilih')
                ->send();
            return false;
        }

        if ($transaction->amount > $latestBalance->remaining_balance) {
            Notification::make()
                ->danger()
                ->title('Saldo Tidak Mencukupi')
                ->body("Saldo tersedia untuk " . $latestBalance->fuelType->name . ": Rp " . number_format($latestBalance->remaining_balance, 0, ',', '.'))
                ->send();
            return false;
        }

        // Only set the balance_id, don't reduce balance yet
        $transaction->balance_id = $latestBalance->id;

        return true;
    }

    public function created(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $balance = Balance::find($transaction->balance_id);

            if (!$balance) {
                return;
            }

            // Update balance after successful transaction creation
            $newBalance = $balance->remaining_balance - $transaction->amount;
            $balance->remaining_balance = $newBalance;
            $balance->save();

            // Trigger widget refresh
            broadcast(new \App\Events\TransactionCreated())->toOthers();
        });
    }

    public function deleting(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            // Get deletion reason from session
            $deletionReason = session('deletion_reason');
            session()->forget('deletion_reason');

            // Save to deleted_transactions table
            DeletedTransaction::create([
                'original_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'user_id' => $transaction->user_id,
                'vehicles_id' => $transaction->vehicles_id,
                'vehicle_type_id' => $transaction->vehicle_type_id,
                'license_plate' => $transaction->license_plate,
                'owner' => $transaction->owner,
                'usage_date' => $transaction->usage_date,
                'fuel_id' => $transaction->fuel_id,
                'fuel_type_id' => $transaction->fuel_type_id,
                'amount' => $transaction->amount,
                'volume' => $transaction->volume,
                'usage_description' => $transaction->usage_description,
                'fuel_receipt' => $transaction->fuel_receipt,
                'invoice' => $transaction->invoice,
                'balance_id' => $transaction->balance_id,
                'deleted_by' => Auth::id(),
                'deletion_reason' => $deletionReason,
                'deleted_at' => now()
            ]);

            // Restore balance
            $balance = Balance::find($transaction->balance_id);
            if ($balance) {
                $newBalance = $balance->remaining_balance + $transaction->amount;
                $balance->remaining_balance = $newBalance;
                $balance->save();
            }

            // Set transaction_number to null BEFORE the soft delete happens
            $transaction->update(['transaction_number' => null]);

            // Let the soft delete happen naturally
            // No need to call forceDelete()

            // Trigger widget refresh
            broadcast(new \App\Events\TransactionCreated())->toOthers();
        });
    }
}

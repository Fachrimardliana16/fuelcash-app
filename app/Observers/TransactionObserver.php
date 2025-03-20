<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Balance;
use Illuminate\Support\Facades\DB;
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
}

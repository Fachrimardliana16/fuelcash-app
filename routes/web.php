<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/transactions/report', App\Http\Controllers\TransactionReportController::class)
    ->name('transactions.report')
    ->middleware(['auth']);

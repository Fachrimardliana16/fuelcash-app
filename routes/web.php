<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/transactions/report', App\Http\Controllers\TransactionReportController::class)
    ->name('transactions.report')
    ->middleware(['auth']);

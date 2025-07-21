<?php

use App\Http\Controllers\Accounts;
use Illuminate\Support\Facades\Route;

Route::post('addInvoice', [Accounts::class, 'addInvoice']);
Route::post('getInvoicesByDateRange', [Accounts::class, 'getInvoicesByDateRange']);
Route::post('addReceipt', [Accounts::class, 'addReceipt']);
Route::post('getReceipts', [Accounts::class, 'getReceipts']);
Route::post('addAccount', [Accounts::class, 'addAccount']);

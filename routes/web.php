<?php

use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\Invoice\Index as InvoiceIndex;
use App\Livewire\Product\Index as ProductIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('products', ProductIndex::class)->name('products.index');
    Route::get('invoices', InvoiceIndex::class)->name('invoices.index');
});

require __DIR__.'/settings.php';

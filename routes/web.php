<?php

use App\Http\Controllers\InvoicePrintController;
use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\ClientService\Index as ClientServiceIndex;
use App\Livewire\Invoice\Index as InvoiceIndex;
use App\Livewire\Product\Index as ProductIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('products', ProductIndex::class)->name('products.index');
    Route::get('invoices', InvoiceIndex::class)->name('invoices.index');
    Route::get('invoices/{invoice}/print', InvoicePrintController::class)->name('invoices.print');

    // Client Services
    Route::get('client-services', ClientServiceIndex::class)->name('client-services.index');

    // System Guide
    Route::view('guide', 'guide')->name('guide');
});

require __DIR__.'/settings.php';

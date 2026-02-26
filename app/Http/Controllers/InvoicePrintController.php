<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoicePrintController extends Controller
{
    /** Handle the incoming request. */
    public function __invoke(Invoice $invoice): \Illuminate\View\View
    {
        $invoice->load(['client', 'items.product']);

        return view('invoice.print', compact('invoice'));
    }
}

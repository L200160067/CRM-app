<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;

class GenerateInvoiceReferenceAction
{
    /**
     * Generate unique final invoice number
     */
    public function execute(Invoice $invoice): string
    {
        if (! $invoice->invoice_number || str_starts_with($invoice->invoice_number, 'TEMP-')) {
            $datePrefix = date('Ymd');
            $invoiceNumber = 'INV-'.$datePrefix.'-'.str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT);

            $invoice->invoice_number = $invoiceNumber;
            // Use quiet to avoid re-triggering recursive events
            $invoice->saveQuietly();
        }

        return $invoice->invoice_number;
    }
}

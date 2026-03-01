<?php

namespace App\Actions\Invoices;

use App\DTOs\Invoice\InvoiceData;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function __construct(
        private readonly GenerateInvoiceReferenceAction $generateReferenceAction,
        private readonly CalculateInvoiceTotalAction $calculateTotalAction
    ) {}

    public function execute(InvoiceData $data, int $authorId): Invoice
    {
        return DB::transaction(function () use ($data, $authorId) {
            // 1. Create the parent Invoice
            $invoice = Invoice::create([
                'client_id' => $data->client_id,
                'created_by' => $authorId,
                'invoice_number' => 'TEMP-'.uniqid(), // Temporary reference
                'issue_date' => $data->issue_date,
                'due_date' => $data->due_date,
                'status' => $data->status,
                'discount_type' => $data->discount_type,
                'discount_rate' => $data->discount_rate,
                'discount' => $data->discount,
                'tax_rate' => $data->tax_rate,
                'notes' => $data->notes,
            ]);

            // 2. Generate Final Invoice Number
            $this->generateReferenceAction->execute($invoice);

            // 3. Insert all Items without triggering observer events to prevent N+1 calculations
            InvoiceItem::withoutEvents(function () use ($invoice, $data) {
                foreach ($data->items as $itemData) {
                    $invoice->items()->create([
                        'product_id' => $itemData->product_id,
                        'item_name' => $itemData->item_name,
                        'quantity' => $itemData->quantity,
                        'unit_price' => $itemData->unit_price,
                        'total_price' => $itemData->quantity * $itemData->unit_price,
                        'description' => $itemData->description,
                    ]);
                }
            });

            // 4. Force final recalculation action manually SATU KALI SAJA
            $this->calculateTotalAction->execute($invoice);

            return $invoice;
        });
    }
}

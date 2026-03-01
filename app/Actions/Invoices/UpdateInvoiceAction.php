<?php

namespace App\Actions\Invoices;

use App\DTOs\Invoice\InvoiceData;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceAction
{
    public function __construct(
        private readonly CalculateInvoiceTotalAction $calculateTotalAction
    ) {}

    public function execute(Invoice $invoice, InvoiceData $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // 1. Update core invoice traits
            $invoice->update([
                'client_id' => $data->client_id,
                'issue_date' => $data->issue_date,
                'due_date' => $data->due_date,
                'status' => $data->status,
                'discount_type' => $data->discount_type,
                'discount_rate' => $data->discount_type === 'percentage' ? $data->discount_rate : null,
                'discount' => $data->discount_type === 'fixed' ? $data->discount : 0,
                'tax_rate' => $data->tax_rate,
                'notes' => $data->notes,
            ]);

            // 2. Synchronize Items
            $receivedIds = $data->items->pluck('id')->filter()->toArray();

            // Gunakan withoutEvents agar proses hapus dan update massal tidak membanjiri server dengan rekalkulasi parsial
            InvoiceItem::withoutEvents(function () use ($invoice, $data, $receivedIds) {
                // Delete missing items
                $invoice->items()->whereNotIn('id', $receivedIds)->delete();

                // Create or update remaining items
                foreach ($data->items as $itemData) {
                    $invoice->items()->updateOrCreate(
                        ['id' => $itemData->id ?? null],
                        [
                            'product_id' => $itemData->product_id,
                            'item_name' => $itemData->item_name,
                            'quantity' => $itemData->quantity,
                            'unit_price' => $itemData->unit_price,
                            'total_price' => $itemData->quantity * $itemData->unit_price,
                            'description' => $itemData->description,
                        ]
                    );
                }
            });

            // 3. One final recalculation SATU KALI SAJA per transaksi update
            $this->calculateTotalAction->execute($invoice);

            return $invoice; // Return fresh invoice (or simply the instance)
        });
    }
}

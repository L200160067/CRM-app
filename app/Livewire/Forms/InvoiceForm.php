<?php

namespace App\Livewire\Forms;

use App\Actions\Invoices\CalculateInvoiceTotalAction;
use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

class InvoiceForm extends Form
{
    public ?Invoice $invoice = null;

    public ?int $client_id = null;

    public ?string $invoice_number = null;

    public ?string $issue_date = null;

    public ?string $due_date = null;

    public ?string $notes = null;

    // Status
    public string $status = 'draft';

    // Discount & Tax Configs
    public string $discount_type = 'fixed';

    public string|float|int $discount_rate = 0;

    public string|float|int $discount = 0;

    public string|float|int $tax_rate = 0;

    // Items Repeater Array for Alpine.js Binding
    public array $items = [
        ['id' => null, 'product_id' => null, 'item_name' => '', 'quantity' => 1, 'unit_price' => 0, 'description' => ''],
    ];

    public function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|string',
            'discount_type' => 'nullable|string',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'items.required' => 'Invoice harus memiliki minimal 1 item/produk.',
            'items.min' => 'Invoice harus memiliki minimal 1 item/produk.',
            'items.*.item_name.required' => 'Nama produk wajib diisi pada baris item.',
            'items.*.quantity.min' => 'Kuantitas minimal 0.01.',
        ];
    }

    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->client_id = $invoice->client_id;
        $this->invoice_number = $invoice->invoice_number;

        $this->issue_date = $invoice->issue_date?->format('Y-m-d');
        $this->due_date = $invoice->due_date?->format('Y-m-d');

        $this->notes = $invoice->notes;
        $this->status = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : $invoice->status;

        $this->discount_type = $invoice->discount_type instanceof DiscountType ? $invoice->discount_type->value : ($invoice->discount_type ?? 'fixed');
        $this->discount_rate = (float) $invoice->discount_rate;
        $this->discount = (float) $invoice->discount;
        $this->tax_rate = (float) $invoice->tax_rate;

        // Populate items for Alpine.js Repeater
        $this->items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'item_name' => $item->item_name,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'description' => $item->description,
            ];
        })->toArray();
    }

    // Helper to generate a dummy/fallback invoice number for new invoices
    protected function generateInvoiceNumber(Invoice $invoice)
    {
        if (! $invoice->invoice_number || str_starts_with($invoice->invoice_number, 'TEMP-')) {
            $datePrefix = date('Ymd');
            $invoice->invoice_number = 'INV-'.$datePrefix.'-'.str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
            $invoice->saveQuietly();
        }
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Create the parent Invoice
            $invoice = Invoice::create([
                'client_id' => $this->client_id,
                'created_by' => auth()->id(),
                'invoice_number' => 'TEMP-'.uniqid(),
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_rate,
                'discount' => $this->discount,
                'tax_rate' => $this->tax_rate,
                'notes' => $this->notes,
            ]);

            $this->generateInvoiceNumber($invoice);

            // 2. Insert all Items (Tanpa memicu event model untuk mencegah N+1 Calculation)
            InvoiceItem::withoutEvents(function () use ($invoice) {
                foreach ($this->items as $itemData) {
                    $invoice->items()->create([
                        'product_id' => empty($itemData['product_id']) ? null : $itemData['product_id'],
                        'item_name' => $itemData['item_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price'], // Wajib diisi manual karena event saving dimatikan
                        'description' => $itemData['description'] ?? null,
                    ]);
                }
            });

            // 3. Force final recalculation action manually SATU KALI SAJA
            app(CalculateInvoiceTotalAction::class)->execute($invoice);
        });

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Update core invoice traits
            $this->invoice->update([
                'client_id' => $this->client_id,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'discount_type' => $this->discount_type,
                'discount_rate' => $this->discount_type === 'percentage' ? $this->discount_rate : null,
                'discount' => $this->discount_type === 'fixed' ? $this->discount : 0,
                'tax_rate' => $this->tax_rate,
                'notes' => $this->notes,
            ]);

            // 2. Synchronize Items
            $receivedIds = collect($this->items)->pluck('id')->filter()->toArray();

            // Gunakan withoutEvents agar proses hapus dan update masal tidak membanjiri server dengan kalkulasi
            InvoiceItem::withoutEvents(function () use ($receivedIds) {
                $this->invoice->items()->whereNotIn('id', $receivedIds)->delete();

                foreach ($this->items as $itemData) {
                    $this->invoice->items()->updateOrCreate(
                        ['id' => $itemData['id'] ?? null],
                        [
                            'product_id' => empty($itemData['product_id']) ? null : $itemData['product_id'],
                            'item_name' => $itemData['item_name'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $itemData['quantity'] * $itemData['unit_price'], // Wajib diisi manual karena event saving dimatikan
                            'description' => $itemData['description'] ?? null,
                        ]
                    );
                }
            });

            // 3. One final action recalculation SATU KALI SAJA
            app(CalculateInvoiceTotalAction::class)->execute($this->invoice);
        });

        $this->reset();
    }
}

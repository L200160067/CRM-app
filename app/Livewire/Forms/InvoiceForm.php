<?php

namespace App\Livewire\Forms;

use App\Actions\Invoices\CreateInvoiceAction;
use App\Actions\Invoices\UpdateInvoiceAction;
use App\DTOs\Invoice\InvoiceData;
use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
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

    public function store()
    {
        $this->validate();

        $data = InvoiceData::fromArray($this->all());

        app(CreateInvoiceAction::class)->execute($data, auth()->id());

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $data = InvoiceData::fromArray($this->all());

        app(UpdateInvoiceAction::class)->execute($this->invoice, $data);

        $this->reset();
    }
}

<?php

namespace App\Livewire\Invoice;

use App\Livewire\Forms\InvoiceForm;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public InvoiceForm $form;

    public string $mode = 'create';

    #[On('load-invoice-form')]
    public function loadInvoice(?int $id = null)
    {
        $this->form->reset();
        $this->form->resetValidation();

        if ($id) {
            $this->mode = 'edit';
            // Eager load items so they can be injected into the internal Repeater Array smoothly
            $invoice = Invoice::with('items')->findOrFail($id);
            $this->authorize('update', $invoice);
            $this->form->setInvoice($invoice);
        } else {
            $this->mode = 'create';
            $this->authorize('create', Invoice::class);
            // Setup default properties
            $this->form->issue_date = now()->format('Y-m-d');
            $this->form->due_date = now()->addDays(14)->format('Y-m-d');
            $this->form->items = [
                // Minimal 1 baris
                ['id' => null, 'product_id' => null, 'item_name' => '', 'quantity' => 1, 'unit_price' => 0, 'description' => ''],
            ];
        }
    }

    public function save()
    {
        if ($this->mode === 'create') {
            $this->authorize('create', Invoice::class);
            $this->form->store();
        } else {
            $this->authorize('update', $this->form->invoice);
            $this->form->update();
        }

        \Flux::modal('invoice-form-modal')->close();

        $this->dispatch('invoice-saved');

        \Flux::toast('Invoice berhasil disimpan.');
    }

    // Provided to views
    #[Computed]
    public function clients()
    {
        return Client::orderBy('name')->get(['id', 'name', 'company_name']);
    }

    #[Computed]
    public function products()
    {
        return Product::orderBy('name')->get(['id', 'name', 'default_price', 'description']);
    }

    public function render()
    {
        return view('livewire.invoice.form');
    }
}

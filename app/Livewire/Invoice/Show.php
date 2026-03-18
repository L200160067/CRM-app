<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public ?Invoice $invoice = null;

    #[On('load-invoice-show')]
    public function loadInvoice(int $id)
    {
        $this->invoice = Invoice::with(['client', 'creator', 'items.product'])->findOrFail($id);
        $this->authorize('view', $this->invoice);
    }

    #[On('invoice-saved')]
    public function refreshInvoice()
    {
        if ($this->invoice) {
            $this->loadInvoice($this->invoice->id);
        }
    }

    public function render()
    {
        return view('livewire.invoice.show');
    }
}

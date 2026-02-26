<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public ?int $invoiceIdToDelete = null;

    public ?int $invoiceIdToForceDelete = null;

    public function confirmDelete(int $id)
    {
        $this->invoiceIdToDelete = $id;
    }

    public function delete()
    {
        if ($this->invoiceIdToDelete) {
            $invoice = Invoice::findOrFail($this->invoiceIdToDelete);
            $this->authorize('delete', $invoice);
            $invoice->delete();

            \Flux::modal('invoice-delete-modal')->close();
            \Flux::toast('Tagihan berhasil dihapus (Dipindahkan ke Trash).');

            $this->invoiceIdToDelete = null;
            unset($this->invoices);
        }
    }

    public function restore(int $id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $invoice);
        $invoice->restore();
        \Flux::toast('Tagihan berhasil dipulihkan dari Trash.');
        unset($this->invoices);
    }

    public function confirmForceDelete(int $id)
    {
        $this->invoiceIdToForceDelete = $id;
    }

    public function forceDelete()
    {
        if ($this->invoiceIdToForceDelete) {
            $invoice = Invoice::onlyTrashed()->findOrFail($this->invoiceIdToForceDelete);
            $this->authorize('forceDelete', $invoice);
            $invoice->forceDelete();

            \Flux::modal('invoice-force-delete-modal')->close();
            \Flux::toast('Tagihan berhasil dihapus secara permanen.');

            $this->invoiceIdToForceDelete = null;
            unset($this->invoices);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    #[Computed]
    public function invoices()
    {
        $query = Invoice::query()->with('client', 'creator');

        if ($this->status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query
            ->when($this->search, function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('client', function ($clientQ) {
                            $clientQ->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('company_name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[On('invoice-saved')]
    public function refreshTable() {}

    public function render()
    {
        return view('livewire.invoice.index');
    }
}

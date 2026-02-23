<?php

namespace App\Livewire\Product;

use App\Models\Product;
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

    public string $status = 'active';

    public ?int $productIdToDelete = null;

    public ?int $productIdToForceDelete = null;

    public function confirmDelete(int $id)
    {
        $this->productIdToDelete = $id;
    }

    public function delete()
    {
        if ($this->productIdToDelete) {
            $product = Product::findOrFail($this->productIdToDelete);
            $this->authorize('delete', $product);
            $product->delete();

            \Flux::modal('product-delete-modal')->close();
            \Flux::toast('Produk berhasil dihapus (Dipindahkan ke Trash).');

            $this->productIdToDelete = null;
            unset($this->products);
        }
    }

    public function restore(int $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $product);
        $product->restore();
        \Flux::toast('Produk berhasil dipulihkan dari Trash.');
        unset($this->products);
    }

    public function confirmForceDelete(int $id)
    {
        $this->productIdToForceDelete = $id;
    }

    public function forceDelete()
    {
        if ($this->productIdToForceDelete) {
            $product = Product::onlyTrashed()->findOrFail($this->productIdToForceDelete);
            $this->authorize('forceDelete', $product);
            $product->forceDelete();

            \Flux::modal('product-force-delete-modal')->close();
            \Flux::toast('Produk berhasil dihapus secara permanen.');

            $this->productIdToForceDelete = null;
            unset($this->products);
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
    public function products()
    {
        $query = Product::query();

        if ($this->status === 'trashed') {
            $query->onlyTrashed();
        }

        return $query
            ->when($this->search, function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[On('product-saved')]
    public function refreshTable() {}

    public function render()
    {
        return view('livewire.product.index');
    }
}

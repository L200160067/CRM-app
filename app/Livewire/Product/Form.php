<?php

namespace App\Livewire\Product;

use App\Livewire\Forms\ProductForm;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ProductForm $form;

    public string $mode = 'create';

    #[On('load-product-form')]
    public function loadProduct(?int $id = null)
    {
        $this->form->reset();
        $this->form->resetValidation();

        if ($id) {
            $this->mode = 'edit';
            $product = Product::findOrFail($id);
            $this->authorize('update', $product);
            $this->form->setProduct($product);
        } else {
            $this->mode = 'create';
            $this->authorize('create', Product::class);
        }
    }

    public function save()
    {
        if ($this->mode === 'create') {
            $this->authorize('create', Product::class);
            $this->form->store();
        } else {
            $this->authorize('update', $this->form->product);
            $this->form->update();
        }

        \Flux::modal('product-form-modal')->close();

        $this->dispatch('product-saved');

        \Flux::toast('Produk berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.product.form');
    }
}

<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public ?Product $product = null;

    #[On('load-product-show')]
    public function loadProduct(int $id)
    {
        $this->product = Product::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.product.show');
    }
}

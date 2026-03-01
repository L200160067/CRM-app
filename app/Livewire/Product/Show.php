<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public ?Product $product = null;

    #[On('load-product-show')]
    public function loadProduct(int $id)
    {
        $this->product = Product::findOrFail($id);
        $this->authorize('view', $this->product);
    }

    public function render()
    {
        return view('livewire.product.show');
    }
}

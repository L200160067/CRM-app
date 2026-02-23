<?php

namespace App\Livewire\Forms;

use App\Models\Product;
use Livewire\Form;

class ProductForm extends Form
{
    public ?Product $product = null;

    public string $name = '';

    public ?string $description = null;

    public string|float|int $default_price = 0;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_price' => 'required|numeric|min:0',
        ];
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->default_price = $product->default_price;
    }

    public function store()
    {
        $this->validate();
        Product::create($this->except('product'));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->product->update($this->except('product'));
        $this->reset();
    }
}

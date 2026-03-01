<?php

namespace App\DTOs\Invoice;

readonly class InvoiceItemData
{
    public function __construct(
        public ?int $id,
        public ?int $product_id,
        public string $item_name,
        public float $quantity,
        public float $unit_price,
        public ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            product_id: empty($data['product_id']) ? null : (int) $data['product_id'],
            item_name: $data['item_name'],
            quantity: (float) $data['quantity'],
            unit_price: (float) $data['unit_price'],
            description: $data['description'] ?? null,
        );
    }
}

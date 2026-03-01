<?php

namespace App\DTOs\Invoice;

use Illuminate\Support\Collection;

readonly class InvoiceData
{
    /**
     * @param  Collection<int, InvoiceItemData>  $items
     */
    public function __construct(
        public int $client_id,
        public string $issue_date,
        public string $due_date,
        public string $status,
        public string $discount_type,
        public float $discount_rate,
        public float $discount,
        public float $tax_rate,
        public ?string $notes,
        public Collection $items,
    ) {}

    public static function fromArray(array $data): self
    {
        $items = collect($data['items'] ?? [])->map(function (array $item) {
            return InvoiceItemData::fromArray($item);
        });

        $discountType = $data['discount_type'] ?? 'fixed';

        return new self(
            client_id: (int) $data['client_id'],
            issue_date: $data['issue_date'],
            due_date: $data['due_date'],
            status: $data['status'],
            discount_type: $discountType,
            discount_rate: $discountType === 'percentage' ? (float) ($data['discount_rate'] ?? 0) : 0.0,
            discount: $discountType === 'fixed' ? (float) ($data['discount'] ?? 0) : 0.0,
            tax_rate: (float) ($data['tax_rate'] ?? 0),
            notes: $data['notes'] ?? null,
            items: $items,
        );
    }
}

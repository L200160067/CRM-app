<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'created_by' => User::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('####'),
            'issue_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => InvoiceStatus::Draft,
            'subtotal' => 0,
            'tax_rate' => 11,
            'tax' => 0,
            'discount_type' => DiscountType::Fixed,
            'discount_rate' => 0,
            'discount' => 0,
            'grand_total' => 0,
            'notes' => null,
            'paid_at' => null,
        ];
    }

    /**
     * Indicate the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate the invoice is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Canceled,
        ]);
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientService>
 */
class ClientServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'product_id' => \App\Models\Product::factory(),
            'domain_name' => $this->faker->domainName(),
            'status' => $this->faker->randomElement(['Active', 'Suspended', 'Expired', 'Pending']),
            'billing_cycle' => $this->faker->randomElement(['Monthly', 'Yearly']),
            'started_at' => $this->faker->date(),
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use Indonesian Faker
        $fakerId = \Faker\Factory::create('id_ID');

        // Kadang klien adalah perorangan (tanpa pt), kadang perusahaan
        $isCompany = $fakerId->boolean(70); // 70% chance it's a company

        $name = $fakerId->name;
        $companyName = $isCompany ? $fakerId->company : null;

        return [
            'name' => $name,
            'company_name' => $companyName,
            'email' => $fakerId->unique()->safeEmail,
            'phone' => $fakerId->phoneNumber,
            'address' => $fakerId->streetAddress,
            'city' => $fakerId->city,
        ];
    }
}

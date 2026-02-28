<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. SuperAdmin — Manajer (akses penuh)
        User::updateOrCreate(
            ['email' => 'admin@m-onesolution.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => Role::SuperAdmin->value,
            ]
        );

        // 2. Admin — Hanya kelola Invoice
        User::updateOrCreate(
            ['email' => 'admin-billing@m-onesolution.com'],
            [
                'name' => 'Admin Billing',
                'password' => Hash::make('password'),
                'role' => Role::Admin->value,
            ]
        );

        // 3. Marketing — Hanya kelola Klien
        User::updateOrCreate(
            ['email' => 'marketing@m-onesolution.com'],
            [
                'name' => 'Tim Marketing',
                'password' => Hash::make('password'),
                'role' => Role::Marketing->value,
            ]
        );

        // 4. Server Manager — Hanya kelola Client Services
        User::updateOrCreate(
            ['email' => 'server@m-onesolution.com'],
            [
                'name' => 'Server Manager',
                'password' => Hash::make('password'),
                'role' => Role::ServerManager->value,
            ]
        );

        // 5. Buat 100 Data Client realistis
        $this->command->info('Seeding 100 Indonesian Clients...');
        Client::factory()->count(100)->create();

        $this->command->info('Seeding typical Software House Products...');
        $this->call([
            ProductSeeder::class,
        ]);

        $this->command->info('Database Seeding Completed Successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                [Role::SuperAdmin->label(), 'admin@m-onesolution.com', 'password'],
                [Role::Admin->label(), 'admin-billing@m-onesolution.com', 'password'],
                [Role::Marketing->label(), 'marketing@m-onesolution.com', 'password'],
                [Role::ServerManager->label(), 'server@m-onesolution.com', 'password'],
            ]
        );
    }
}

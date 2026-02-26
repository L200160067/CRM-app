<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Level Admin 1
        User::updateOrCreate(
            ['email' => 'admin@m-onesolution.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Buat Akun User Reguler 1
        User::updateOrCreate(
            ['email' => 'staff@m-onesolution.com'],
            [
                'name' => 'Staff Operasional',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );

        // 3. Buat 100 Data Client dengan Bahasa Indonesia yang realistis
        $faker = Faker::create('id_ID');

        // Loop directly or use Factory
        // Since we created ClientFactory, we can just use the factory.
        // But to be sure it uses id_ID, the factory already configures id_ID.
        $this->command->info('Seeding 100 Indonesian Clients...');
        Client::factory()->count(100)->create();

        $this->command->info('Seeding typical Software House Products...');
        $this->call([
            ProductSeeder::class,
        ]);

        $this->command->info('Database Seeding Completed Successfully!');
    }
}

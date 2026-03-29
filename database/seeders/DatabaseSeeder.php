<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        User::updateOrCreate(
            ['email' => 'media@example.com'],
            [
                'name' => 'Media User',
                'password' => bcrypt('password'),
                'role' => 'media',
            ]
        );
        // Seed in the correct order based on dependencies
        // $this->call([
        //     UserSeeder::class,
        //     HallSeeder::class,
        //     HallImageSeeder::class,
        //     BookingSeeder::class,
        //     PaymentSeeder::class,
        // ]);
    }
}

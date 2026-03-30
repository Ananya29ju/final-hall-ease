<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Hall;
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

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Admin'],
            ['name' => 'Admin Hall', 'capacity' => 120, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Maffie'],
            ['name' => 'Maffie Hall', 'capacity' => 100, 'description' => 'Maffie Block halls at Main Campus.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Aruppe'],
            ['name' => 'Aruppe Hall', 'capacity' => 90, 'description' => 'Aruppe Block halls at Main Campus.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Xavier'],
            ['name' => 'Xavier Hall', 'capacity' => 110, 'description' => 'Xavier Block halls at Main Campus.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'LCRI'],
            ['name' => 'LCRI Hall', 'capacity' => 130, 'description' => 'LCRI Block halls at Main Campus.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'AIMIT Campus', 'location' => 'AIMIT'],
            ['name' => 'AIMIT Hall', 'capacity' => 100, 'description' => 'AIMIT Campus hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Engineering Campus', 'location' => 'Engineering'],
            ['name' => 'Engineering Hall', 'capacity' => 120, 'description' => 'Engineering Campus hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Capitano Campus', 'location' => 'Capitano'],
            ['name' => 'Capitano Hall', 'capacity' => 110, 'description' => 'Capitano Campus hall.', 'status' => 'available']
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

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
            ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Sahodhaya'],
            ['capacity' => 170, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Sanidhya'],
            ['capacity' => 100, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Magis'],
            ['capacity' => 60, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Peace Park'],
            ['capacity' => 200, 'description' => 'Main Campus open area space.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Altorium'],
            ['capacity' => 700, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Xavier', 'name' => 'Xavier Hall'],
            ['capacity' => 130, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'LCRI', 'name' => 'Robert Sequeira'],
            ['capacity' => 140, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'LCRI', 'name' => 'L. F. Rasuinha'],
            ['capacity' => 450, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Maffei', 'name' => 'Eric Mathais'],
            ['capacity' => 255, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Maffei', 'name' => 'Joseph Willy'],
            ['capacity' => 110, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Aruppe', 'name' => 'AV Room'],
            ['capacity' => 100, 'description' => 'Audio Visual room.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Aruppe', 'name' => 'AR 801'],
            ['capacity' => 250, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Aruppe', 'name' => 'AR 803'],
            ['capacity' => 400, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
        );

        Hall::updateOrCreate(
            ['campus_name' => 'Main Campus', 'location' => 'Aruppe', 'name' => 'AR 804'],
            ['capacity' => 600, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
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

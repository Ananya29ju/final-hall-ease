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
    ['capacity' => 150, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Magis'],
    ['capacity' => 120, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Peace Park'],
    ['capacity' => 200, 'description' => 'Main Campus open area space.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Altorium'],
    ['capacity' => 180, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Xavier Hall'],
    ['capacity' => 160, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Robert Sequeira'],
    ['capacity' => 140, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'L. F. Rasuinha'],
    ['capacity' => 130, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Eric Mathais'],
    ['capacity' => 130, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'Joseph Willy'],
    ['capacity' => 130, 'description' => 'Main Campus Admin Block hall.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'AV Room'],
    ['capacity' => 80, 'description' => 'Audio Visual room.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'AR 801'],
    ['capacity' => 60, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'AR 803'],
    ['capacity' => 60, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
);

Hall::updateOrCreate(
    ['campus_name' => 'Main Campus', 'location' => 'Admin', 'name' => 'AR 804'],
    ['capacity' => 60, 'description' => 'Classroom in Admin Block.', 'status' => 'available']
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

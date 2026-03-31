<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some admin users
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create some regular users
        User::factory()
            ->state(['role' => 'user'])
            ->count(15)
            ->create();

        // Create specific edu.in accounts for testing
        $dummies = [
            ['name' => 'Admin One', 'email' => 'admin1@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'admin', 'status' => 'approved'],
            ['name' => 'Admin Two', 'email' => 'admin2@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'admin', 'status' => 'approved'],
            ['name' => 'Staff One', 'email' => 'staff1@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'user', 'status' => 'approved'],
            ['name' => 'Staff Two', 'email' => 'staff2@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'user', 'status' => 'approved'],
            ['name' => 'Media One', 'email' => 'media1@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'media', 'status' => 'approved'],
            ['name' => 'Media Two', 'email' => 'media2@staloysius.edu.in', 'password' => \Illuminate\Support\Facades\Hash::make('password123'), 'role' => 'media', 'status' => 'approved'],
        ];

        foreach ($dummies as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}

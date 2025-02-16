<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'userName' => 'johndoe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('!JohnDoe2025'),
            'phoneNumber' => '1234567890',
            'role' => 'admin',
        ]);

        // Regular User
        User::create([
            'firstName' => 'Marry',
            'lastName' => 'Smith',
            'username' => 'marrysmith',
            'email' => 'marrysmith@example.com',
            'password' => Hash::make('!MarrySmith2025'),
            'phoneNumber' => '9876543210',
            'role' => 'user',
        ]);
    }
}

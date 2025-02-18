<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Festival;

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

        // Festivals details
        $festivals = [
            [
                'name' => 'Jazz Festival',
                'description' => 'Experience the best jazz music in Haarlem',
                'image_path' => 'festivals/jazz.jpg'
            ],
            [
                'name' => 'Food Festival',
                'description' => 'Taste the finest cuisine Haarlem has to offer',
                'image_path' => 'festivals/food.jpg'
            ],
            [
                'name' => 'Dance Festival',
                'description' => 'Dance the night away in Haarlem',
                'image_path' => 'festivals/dance.jpg'
            ],
            [
                'name' => 'Historic Festival',
                'description' => 'Discover Haarlem\'s rich history',
                'image_path' => 'festivals/historic.jpg'
            ]
        ];

        // Populate festivals table
        foreach ($festivals as $festival) {
            Festival::create($festival);
        }
    }
}

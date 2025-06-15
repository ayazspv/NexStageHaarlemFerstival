<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Festival;
use App\Models\HomepageContent;
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

        // Employee user
        User::create([
            'firstName' => 'Ashley',
            'lastName' => 'James',
            'username' => 'ashleyjames',
            'email' => 'ashleyjames@example.com',
            'password' => Hash::make('!AshleyJames2025'),
            'phoneNumber' => '323232323',
            'role' => 'employee',
        ]);


        // Festivals details
        $festivals = [
            [
                'name' => 'Jazz Festival',
                'description' => 'Experience the rhythm of Haarlem this summer with unforgettable live performances, vibrant city stages, and a celebration of music, art, and culture at the iconic Haarlem Jazz Festival.',
                'image_path' => 'festivals/jazz.jpg',
                'festivalType' => 0,
                'ticket_amount' => 250,
                'time_slot' => '18:00 - 22:00',
            ],
            [
                'name' => 'Food Festival',
                'description' => 'Curious about the dining options available at Haarlem? Explore what renowned Haarlem restaurants from this year has to offer. Indulge your palate in a culinary journey with these exquisite dining experiences.',
                'image_path' => 'festivals/food.jpg',
                'festivalType' => 1,
                'ticket_amount' => 150,
                'time_slot' => '16:00 - 18:00',
            ],
            [
                'name' => 'A Stroll Through History',
                'description' => 'A Stroll Through History is a tour within the Haarlem festival events, with 10 stops starting with the Church of St. Bavo ending at the Hof van Bakenes. The tour comes with English, Dutch or Chinese guide.',
                'image_path' => 'festivals/historic.jpg',
                'festivalType' => 2,
                'ticket_amount' => 100,
                'time_slot' => '10:00 - 16:00',
            ],
            [
                'name' => 'Night@Teylers',
                'description' => 'In the historic city of Haarlem, the brilliant Dr. Teyler created a museum filled with extraordinary treasures - sparkling minerals, fascinating machines, and beautiful works of art. Can you solve all the mysteries that Dr Teylers left?',
                'image_path' => 'festivals/teyler.jpg',
                'isGame' => true,
                'festivalType' => 3,
                'time_slot' => '10:00 - 17:00',
            ],
        ];

        // Populate festivals table
        foreach ($festivals as $festival) {
            Festival::create($festival);
        }

        // Add HomepageContent seeder
        HomepageContent::create([
            'content' => '<h1>What is Haarlem Festival?</h1><p>Join the Haarlem Festival and experience four unforgettable days celebrating everything that makes Haarlem unique! From mouth-watering food and rich history to vibrant music, enjoy the very best this city has to offer all in one festival. Scroll down to discover the exciting events waiting for you!</p>',
            'hero_image_path' => 'festivals/hero.png'
        ]);
    }
}

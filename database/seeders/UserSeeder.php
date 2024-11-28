<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $departments = ['CEIT', 'GSOLC', 'CAFENR', 'CAS', 'CCJ', 'CEMDS', 'CED', 'CON', 'CVMBS'];
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@cvsu.edu.ph',
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('password123'),
        //     'utype' => 'ADM',
        //     'password_set' => true,
        // ]);

        for ($i = 1; $i <= 4; $i++) { 
            User::create([
                'name' => 'Admin User ' . $i,  // Name changes for each admin
                'email' => 'admin' . $i . '@cvsu.edu.ph',  // Dynamic email based on $i
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'utype' => 'ADM',
                'password_set' => true,
            ]);
        }

        User::create([
            'name' => 'Director User',
            'email' => 'director@cvsu.edu.ph',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'utype' => 'DIR',
            'password_set' => true,
        ]);

        // for ($i = 1; $i <= 19; $i++) {
        //     User::create([
        //         'name' => $faker->name,
        //         'email' => "user{$i}@cvsu.edu.ph",
        //         'email_verified_at' => now(),
        //         'password' => Hash::make('password123'),
        //         'utype' => 'USR',
        //         'password_set' => false,
        //         'phone_number' => '9' . $faker->numerify('#########'),
        //         'year_level' => $faker->randomElement($yearLevels),
        //         'department' => $faker->randomElement($departments),
        //         'course' => $faker->sentence(3),
        //         'profile_image' => null, 
        //     ]);
        // }

    }
}

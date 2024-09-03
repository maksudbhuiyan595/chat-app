<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            "name"=> "Admin",
            "email"=> "admin@gmail.com",
            "password"=> bcrypt("123456"),
            "role"=>1,
        ]);
        User::create([
            "name"=> "User2",
            "email"=> "user2@gmail.com",
            "password"=> bcrypt("123456"),
            "role"=>0,
        ]);
        User::create([
            "name"=> "User3",
            "email"=> "user3@gmail.com",
            "password"=> bcrypt("123456"),
            "role"=>0,
        ]);
        User::create([
            "name"=> "User4",
            "email"=> "user4@gmail.com",
            "password"=> bcrypt("123456"),
            "role"=>0,
        ]);
    }
}

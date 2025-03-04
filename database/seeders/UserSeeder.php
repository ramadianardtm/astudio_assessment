<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Ramadian',
            'last_name' => 'Arditama',
            'email' => 'ramadianardtm@gmail.com',
            'password' => Hash::make('password123'),
        ]);
    }
}

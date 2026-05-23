<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Demo Seller',
            'email' => 'seller@tixloop.test',
            'password' => Hash::make('password'),
        ])->assignRole('seller');
    }
}

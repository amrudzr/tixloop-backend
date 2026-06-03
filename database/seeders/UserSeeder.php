<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core Personas (5)
        $admin = User::create([
            'name' => 'Admin Verifikator',
            'email' => 'admin@tixloop.com',
            'phone' => '+6281111111111',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'student.seller@tixloop.com',
            'phone' => '+6282222222222',
            'password' => Hash::make('password'),
        ]);
        $student->assignRole('seller');

        $enthusiast = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'music.enthusiast@tixloop.com',
            'phone' => '+6283333333333',
            'password' => Hash::make('password'),
        ]);
        $enthusiast->assignRole('seller');
        $enthusiast->assignRole('buyer');

        $hunter = User::create([
            'name' => 'Siti Rahma',
            'email' => 'concert.hunter@tixloop.com',
            'phone' => '+6284444444444',
            'password' => Hash::make('password'),
        ]);
        $hunter->assignRole('buyer');

        $collector = User::create([
            'name' => 'Diana Putri',
            'email' => 'event.collector@tixloop.com',
            'phone' => '+6285555555555',
            'password' => Hash::make('password'),
        ]);
        $collector->assignRole('buyer');

        // Additional Factory Users (11) to meet target of 16 users
        User::factory()->count(6)->create()->each(function ($user) {
            $user->assignRole('seller');
        });

        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('buyer');
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario admin demo
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Usuario Maria demo
        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name' => 'Maria',
                'password' => Hash::make('password'),
            ]
        );
    }
}

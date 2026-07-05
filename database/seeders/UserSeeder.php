<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('spaifinance123'),
                'pin' => '112233',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'pengurus'],
            [
                'name' => 'Pengurus Yayasan',
                'password' => Hash::make('pengurus123'),
                'pin' => '112233',
                'role' => 'pengurus',
            ]
        );
    }
}

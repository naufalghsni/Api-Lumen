<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'username' => 'administrator',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('admin'),
        //     'role' => 'admin',
        // ]);
        
        User::create([
            'username' => 'staff',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'name' => 'Felipe Rubilar',
            'email' => 'frubilar@rhinnoit.cloud',
            'email_verified_at' => now(),
            'password' => Hash::make('F3l1p3#Rub1l4r!'),
            'dni' => '17353223-7',
            'phone' => '+56966107777',
        ]);

        $user->assignRole('Administrator');

    }
}

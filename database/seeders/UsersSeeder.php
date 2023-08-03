<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{

    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'phone' => '+00(000)000-00-00',
            'role_id' => 1,
            'language' => config('app.locale'),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
    }
}

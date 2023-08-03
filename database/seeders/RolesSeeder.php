<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            [
               'id' => 1,
               'role' => 'ADMIN',
               'role_slug' => 'administrator',
            ],
            [
              'id' => 2,
              'role' => 'USER',
              'role_slug' => 'user',
            ],
        ]);
    }
}

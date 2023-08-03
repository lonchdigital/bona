<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MultiColorsColorSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        Color::create([
            'creator_id' => $creator->id,
            'name' => [
                'uk' => 'Різнокольоровий',
                'ru' => 'Разноцветный',
            ],
            'slug' => Str::slug('Різнокольоровий')
        ]);
    }
}

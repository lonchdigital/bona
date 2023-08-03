<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            [
                'name' => [
                    'uk' => 'Червоний',
                    'ru' => 'Красный',
                ],
                'slug' => 'cervony',
                'hex' => '#f16464',
            ],
            [
                'name' => [
                    'uk' => 'Помаранчевий',
                    'ru' => 'Оранжевый',
                ],
                'slug' => 'pomarancevy',
                'hex' => '#ff9345',
            ],
            [
                'name' => [
                    'uk' => 'Жовтий',
                    'ru' => 'Желтый',
                ],
                'slug' => 'zovty',
                'hex' => '#feda1e',
            ],
            [
                'name' => [
                    'uk' => 'Зелений',
                    'ru' => 'Зеленый',
                ],
                'slug' => 'zeleny',
                'hex' => '#579965',
            ],
            [
                'name' => [
                    'uk' => 'Блакитний',
                    'ru' => 'Голубой',
                ],
                'slug' => 'blakytny',
                'hex' => '#3b87b1',
            ],
            [
                'name' => [
                    'uk' => 'Синій',
                    'ru' => 'Синий',
                ],
                'slug' => 'syniy',
                'hex' => '#22294f',
            ],
            [
                'name' => [
                    'uk' => 'Білий',
                    'ru' => 'Белый',
                ],
                'slug' => 'bily',
                'hex' => '#fff',
            ],
            [
                'name' => [
                    'uk' => 'Чорний',
                    'ru' => 'Черный',
                ],
                'slug' => 'corny',
                'hex' => '#1d1d23',
            ],
            [
                'name' => [
                    'uk' => 'Сірий',
                    'ru' => 'Серый',
                ],
                'slug' => 'siry',
                'hex' => '#88888e',
            ],
            [
                'name' => [
                    'uk' => 'Фіолетовий',
                    'ru' => 'Фиолетовый',
                ],
                'slug' => 'fioletovy',
                'hex' => '#8f667c',
            ],
            [
                'name' => [
                    'uk' => 'Молочний',
                    'ru' => 'Молочный',
                ],
                'slug' => 'molocny',
                'hex' => '#f1e0d0',
            ],
            [
                'name' => [
                    'uk' => 'Рожевий',
                    'ru' => 'Розовый',
                ],
                'slug' => 'rozevy',
                'hex' => '#f1d7ef',
            ],
            [
                'name' => [
                    'uk' => 'Градієнт',
                    'ru' => 'Градиент',
                ],
                'slug' => 'gradint',
                'hex' => '#4158d0',
            ],
            [
                'name' => [
                    'uk' => 'Світло-рожевий',
                    'ru' => 'Светло-розовый',
                ],
                'slug' => 'svitlo-rozev',
                'hex' => '#e8bbb7',
            ],
            [
                'name' => [
                    'uk' => 'Березовий',
                    'ru' => 'Березовый',
                ],
                'slug' => 'berezovy',
                'hex' => '#abdcd7',
            ],
            [
                'name' => [
                    'uk' => 'Світло-чорний',
                    'ru' => 'Светло-черный',
                ],
                'slug' => 'svitlo-corny',
                'hex' => '#746666',
            ],
            [
                'name' => [
                    'uk' => 'Світло-синій',
                    'ru' => 'Светло-синий',
                ],
                'slug' => 'svitlo-syni',
                'hex' => '#6c7aa0',
            ],
            [
                'name' => [
                    'uk' => 'Світло-сірий',
                    'ru' => 'Светло-серый',
                ],
                'slug' => 'svitlo-siry',
                'hex' => '#f0f1f3',
            ],
            [
                'name' => [
                    'uk' => 'Темно-білий',
                    'ru' => 'Темно-белый',
                ],
                'slug' => 'temno-bily',
                'hex' => '#f3f3f3',
            ],
            [
                'name' => [
                    'uk' => 'Світло-помаранчевий',
                    'ru' => 'Светло-оранжевый',
                ],
                'slug' => 'svitlo-pomarancevy',
                'hex' => '#fbe9db',
            ],
        ];

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        foreach ($colors as $color) {
            $color['creator_id'] = $creator->id;
            Color::create($color);
        }
    }
}

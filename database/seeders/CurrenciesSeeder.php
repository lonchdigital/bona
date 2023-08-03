<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => [
                    'uk' => 'Гривня',
                    'ru' => 'Гривна',
                ],
                'name_short' => [
                    'uk' => 'грн.',
                    'ru' => 'грн.',
                ],
                'code' => 'UAH',
                'is_base' => true,
            ],
            [
                'name' => [
                    'uk' => 'Долар',
                    'ru' => 'Доллар',
                ],
                'name_short' => [
                    'uk' => 'дол.',
                    'ru' => 'дол.',
                ],
                'code' => 'USD',
                'rate' => 37.40,
            ],
            [
                'name' => [
                    'uk' => 'Євро',
                    'ru' => 'Евро',
                ],
                'name_short' => [
                    'uk' => 'євр.',
                    'ru' => 'евр.',
                ],
                'code' => 'EUR',
                'rate' => 40.62,
            ],
        ];

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        foreach ($currencies as $currency) {
            $currency['creator_id'] = $creator->id;
            Currency::create($currency);
        }
    }
}

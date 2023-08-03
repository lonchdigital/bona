<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(ColorsSeeder::class);
        $this->call(CurrenciesSeeder::class);
        $this->call(ProductFieldsSeeder::class);
        $this->call(ProductTypesSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(BrandsSeeder::class);
        $this->call(CollectionsSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(RegionsSeeder::class);
        $this->call(MultiColorsColorSeeder::class);
        $this->call(SeogenConfigsSeeder::class);
    }
}

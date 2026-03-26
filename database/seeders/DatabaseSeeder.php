<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      $this->call([
        RoleSeeder::class,
        CountrySeeder::class,
        CitySeeder::class,
        CommuneSeeder::class,
        AdministrationSeeder::class,
        ServiceSeeder::class,

    ]);

    User::factory()->withPhone('0767189538')
                    ->withRole('user')
                     ->create(['first_name' => 'Alice','last_name' => 'Dupont']);

  User::factory()->withPhone('0202020202')
                  ->withRole('admin')
                  ->create(['first_name' => 'Bob','last_name' => 'Martin']);

    }
}

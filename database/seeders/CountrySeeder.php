<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = config('data.countries');

        $payload = [];

        foreach ($countries as $country) {

            $name = $country;

            $payload[] = [
                'key' => strtoupper(Str::substr(Str::slug($name), 0, 3)) . Str::random(10),
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Country::upsert(
            $payload,
            ['key'],
            ['name', 'slug']
        );
    }
}

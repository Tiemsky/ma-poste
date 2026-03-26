<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $countryName = "Côte d’Ivoire";

        $country = Country::where('slug', Str::slug($countryName))
            ->firstOrFail();

        $cities = config('data.ivory_coast.cities');

        $payload = [];

        foreach ($cities as $city) {

            $name = is_array($city) ? $city['name'] : $city;

            $payload[] = [
                'key' => strtoupper(Str::substr(Str::slug($name), 0, 3))  . Str::random(10),
                'name' => $name,
                'slug' => Str::slug($name),
                'country_id' => $country->id,
                'has_communes' => is_array($city) ? ($city['has_communes'] ?? false) : false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        City::upsert(
            $payload,
            ['key', 'country_id'],
            ['name', 'slug', 'has_communes']
        );
    }
}
